<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceRecapExport;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Kelas;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceController extends Controller
{
    /**
     * Display attendance history for teachers/admins, or the student's own history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return $this->studentHistory($user);
        }

        $query = Attendance::query()
            ->with(['schedule.assignment.mapel', 'schedule.assignment.kelas', 'teacher', 'details'])
            ->latest('date')
            ->latest('id');

        if (! $user->isSuperAdmin()) {
            $query->where('teacher_id', $user->id);
        }

        if ($request->filled('kelas')) {
            $kelasId = $request->integer('kelas');
            $query->whereHas('schedule.assignment', fn ($query) => $query->where('kelas_id', $kelasId));
        }

        $kelasList = $user->isSuperAdmin()
            ? Kelas::orderBy('name')->get()
            : Kelas::whereIn('id', TeachingAssignment::where('teacher_id', $user->id)->pluck('kelas_id'))->orderBy('name')->get();

        return view('pages.presensi.index', [
            'mode' => 'manage',
            'attendances' => $query->paginate(10)->withQueryString(),
            'kelasList' => $kelasList,
        ]);
    }

    /**
     * Show the attendance form for a class schedule.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $schedules = TeachingSchedule::query()
            ->with(['assignment.mapel', 'assignment.kelas', 'assignment.teacher'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->whereHas(
                'assignment',
                fn ($query) => $query->where('teacher_id', $user->id)
            ))
            ->orderByRaw("CASE day WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 ELSE 7 END")
            ->orderBy('start_time')
            ->get();

        $date = $request->string('date')->toString();
        if (! Carbon::hasFormat($date, 'Y-m-d')) {
            $date = now()->toDateString();
        }

        $selectedSchedule = $request->filled('schedule')
            ? $schedules->firstWhere('id', $request->integer('schedule'))
            : null;

        $students = collect();
        $existing = null;

        if ($selectedSchedule !== null) {
            $students = User::query()
                ->where('role', 'murid')
                ->where('kelas_id', $selectedSchedule->assignment?->kelas_id)
                ->orderBy('name')
                ->get();

            $existing = Attendance::query()
                ->where('schedule_id', $selectedSchedule->id)
                ->whereDate('date', $date)
                ->with('details')
                ->first();
        }

        return view('pages.presensi.create', [
            'schedules' => $schedules,
            'students' => $students,
            'selectedSchedule' => $selectedSchedule,
            'existing' => $existing,
            'date' => $date,
            'statuses' => Attendance::STATUSES,
        ]);
    }

    /**
     * Store a new attendance record and its student details.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:teaching_schedules,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.status' => ['required', 'in:H,I,S,A'],
            'details.*.reason' => ['nullable', 'string', 'max:255'],
        ], [
            'schedule_id.required' => 'Jadwal wajib dipilih.',
            'details.required' => 'Daftar siswa wajib diisi.',
            'details.*.status.in' => 'Status kehadiran harus salah satu dari H, I, S, atau A.',
        ]);

        $schedule = TeachingSchedule::with('assignment')->findOrFail($validated['schedule_id']);
        $user = $request->user();

        if (! $user->isSuperAdmin() && $schedule->assignment?->teacher_id !== $user->id) {
            abort(403, 'Anda tidak mengajar pada jadwal ini.');
        }

        $kelasId = $schedule->assignment?->kelas_id;

        if ($kelasId === null) {
            throw ValidationException::withMessages([
                'schedule_id' => 'Jadwal belum terhubung dengan kelas.',
            ]);
        }

        $studentIds = User::query()
            ->where('role', 'murid')
            ->where('kelas_id', $kelasId)
            ->whereIn('id', array_keys($validated['details']))
            ->pluck('id');

        if ($studentIds->count() !== count($validated['details'])) {
            throw ValidationException::withMessages([
                'details' => 'Daftar siswa tidak sesuai dengan kelas pada jadwal ini.',
            ]);
        }

        $alreadyRecorded = Attendance::query()
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($alreadyRecorded) {
            throw ValidationException::withMessages([
                'date' => 'Presensi untuk jadwal dan tanggal ini sudah pernah disimpan.',
            ]);
        }

        $attendance = DB::transaction(function () use ($request, $schedule, $validated) {
            $attendance = Attendance::create([
                'schedule_id' => $schedule->id,
                'teacher_id' => $request->user()->id,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['details'] as $studentId => $detail) {
                AttendanceDetail::create([
                    'attendance_id' => $attendance->id,
                    'student_id' => (int) $studentId,
                    'status' => $detail['status'],
                    'reason' => $detail['reason'] ?? null,
                ]);
            }

            return $attendance;
        });

        ActivityLog::record(
            'attendance.created',
            "Presensi {$attendance->schedule?->assignment?->mapel?->name} kelas {$attendance->schedule?->assignment?->kelas?->name} tanggal {$attendance->date->format('d M Y')} disimpan",
            $attendance
        );

        return redirect()
            ->route('presensi.show', $attendance)
            ->with('success', 'Presensi berhasil disimpan!');
    }

    /**
     * Display one attendance record.
     */
    public function show(Request $request, Attendance $attendance): View
    {
        $attendance->load([
            'schedule.assignment.mapel',
            'schedule.assignment.kelas',
            'teacher',
            'details.student',
        ]);

        $user = $request->user();
        $ownDetail = null;

        if ($user->isStudent()) {
            $ownDetail = $attendance->details->firstWhere('student_id', $user->id);
            abort_if($ownDetail === null, 403, 'Anda tidak terdaftar pada presensi ini.');
        } elseif (! $user->isSuperAdmin() && $attendance->teacher_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat presensi yang Anda buat.');
        }

        return view('pages.presensi.show', [
            'attendance' => $attendance,
            'ownDetail' => $ownDetail,
        ]);
    }

    /**
     * Show the monthly attendance recap.
     */
    public function recap(Request $request): View
    {
        $data = $this->recapData($request);

        return view('pages.presensi.recap', $data);
    }

    /**
     * Download the monthly attendance recap as an Excel file.
     */
    public function exportRecap(Request $request): BinaryFileResponse
    {
        $data = $this->recapData($request);

        return Excel::download(
            new AttendanceRecapExport(
                $data['students'],
                $data['recap'],
                $data['selectedKelas']?->name,
            ),
            'rekap-presensi-'.$data['month'].'.xlsx'
        );
    }

    /**
     * Build the attendance history view for a student.
     */
    private function studentHistory(User $user): View
    {
        $details = AttendanceDetail::query()
            ->select('attendance_details.*')
            ->join('attendances', 'attendances.id', '=', 'attendance_details.attendance_id')
            ->where('attendance_details.student_id', $user->id)
            ->with('attendance.schedule.assignment.mapel', 'attendance.schedule.assignment.kelas')
            ->orderByDesc('attendances.date')
            ->orderByDesc('attendances.id')
            ->paginate(15);

        $summary = AttendanceDetail::query()
            ->where('student_id', $user->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $summary->sum();
        $present = (int) $summary->get('H', 0);

        return view('pages.presensi.index', [
            'mode' => 'student',
            'details' => $details,
            'summary' => $summary,
            'total' => $total,
            'presentPercentage' => $total > 0 ? (int) round($present / $total * 100) : 0,
        ]);
    }

    /**
     * Gather recap data shared by the recap page and its export.
     *
     * @return array<string, mixed>
     */
    private function recapData(Request $request): array
    {
        $user = $request->user();

        $month = $request->string('month')->toString();
        if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
            $month = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $classIds = $user->isSuperAdmin()
            ? null
            : TeachingAssignment::where('teacher_id', $user->id)->pluck('kelas_id')->unique();

        $kelasList = Kelas::query()
            ->when($classIds !== null, fn ($query) => $query->whereIn('id', $classIds))
            ->orderBy('name')
            ->get();

        $selectedKelas = $request->filled('kelas')
            ? $kelasList->firstWhere('id', $request->integer('kelas'))
            : null;

        $students = $this->recapStudents($kelasList, $selectedKelas);
        $recap = $this->recapCounts($students->pluck('id'), $start, $end);

        return [
            'students' => $students,
            'recap' => $recap,
            'month' => $month,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'statuses' => Attendance::STATUSES,
        ];
    }

    /**
     * @param  Collection<int, Kelas>  $kelasList
     * @return Collection<int, User>
     */
    private function recapStudents(Collection $kelasList, ?Kelas $selectedKelas): Collection
    {
        return User::query()
            ->where('role', 'murid')
            ->whereIn('kelas_id', $selectedKelas !== null ? [$selectedKelas->id] : $kelasList->pluck('id'))
            ->with('kelas')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, int>  $studentIds
     * @return array<int, array<string, int>>
     */
    private function recapCounts(Collection $studentIds, Carbon $start, Carbon $end): array
    {
        if ($studentIds->isEmpty()) {
            return [];
        }

        $rows = AttendanceDetail::query()
            ->join('attendances', 'attendances.id', '=', 'attendance_details.attendance_id')
            ->whereIn('attendance_details.student_id', $studentIds)
            ->whereBetween('attendances.date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('attendance_details.student_id, attendance_details.status, COUNT(*) as total')
            ->groupBy('attendance_details.student_id', 'attendance_details.status')
            ->get();

        $recap = [];

        foreach ($rows as $row) {
            $recap[(int) $row->student_id][$row->status] = (int) $row->total;
        }

        return $recap;
    }
}
