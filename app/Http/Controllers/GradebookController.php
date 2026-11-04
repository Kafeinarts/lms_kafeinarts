<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\ExamSubmission;
use App\Models\FinalGrade;
use App\Models\GradeComponent;
use App\Models\GradeWeight;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SchoolProfile;
use App\Models\TeachingAssignment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradebookController extends Controller
{
    /**
     * Display the gradebook overview.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return redirect()->route('nilai.rapor');
        }

        $academicYear = AcademicYear::current();
        $mapels = $this->accessibleMapels($user);

        $weights = GradeWeight::query()
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('mapel_id', $mapels->pluck('id'))
            ->get()
            ->keyBy('mapel_id');

        $assignments = TeachingAssignment::query()
            ->with('kelas')
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('teacher_id', $user->id))
            ->get()
            ->groupBy('mapel_id');

        return view('pages.nilai.index', [
            'academicYear' => $academicYear,
            'mapels' => $mapels,
            'weights' => $weights,
            'assignments' => $assignments,
            'academicYears' => AcademicYear::orderByDesc('id')->get(),
        ]);
    }

    /**
     * Show the weight configuration form for a subject.
     */
    public function weights(Request $request): View
    {
        $mapel = Mapel::findOrFail($request->integer('mapel'));
        $this->ensureTeacherTeachesMapel($request->user(), $mapel->id);

        $academicYears = AcademicYear::orderByDesc('id')->get();
        $academicYear = $request->filled('academic_year')
            ? AcademicYear::findOrFail($request->integer('academic_year'))
            : AcademicYear::current();

        $weight = GradeWeight::query()
            ->where('mapel_id', $mapel->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        return view('pages.nilai.weights', [
            'mapel' => $mapel,
            'weight' => $weight,
            'academicYear' => $academicYear,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Store the weight configuration for a subject.
     */
    public function storeWeights(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => ['required', 'exists:mapels,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'assignment_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'uts_weight' => ['required', 'integer', 'min:0', 'max:100'],
            'uas_weight' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'assignment_weight.required' => 'Bobot tugas wajib diisi.',
            'uts_weight.required' => 'Bobot UTS wajib diisi.',
            'uas_weight.required' => 'Bobot UAS wajib diisi.',
        ]);

        $total = $validated['assignment_weight'] + $validated['uts_weight'] + $validated['uas_weight'];

        if ($total !== 100) {
            throw ValidationException::withMessages([
                'assignment_weight' => 'Total bobot tugas, UTS, dan UAS harus 100%.',
            ]);
        }

        $this->ensureTeacherTeachesMapel($request->user(), $validated['mapel_id']);

        $mapel = Mapel::findOrFail($validated['mapel_id']);

        $weight = GradeWeight::updateOrCreate(
            [
                'mapel_id' => $mapel->id,
                'academic_year_id' => $validated['academic_year_id'],
            ],
            [
                'assignment_weight' => $validated['assignment_weight'],
                'uts_weight' => $validated['uts_weight'],
                'uas_weight' => $validated['uas_weight'],
            ]
        );

        ActivityLog::record(
            'grade_weight.updated',
            "Bobot penilaian {$mapel->name} diatur ({$validated['assignment_weight']}% tugas, {$validated['uts_weight']}% UTS, {$validated['uas_weight']}% UAS)",
            $weight
        );

        return redirect()->route('nilai.index')->with('success', "Bobot penilaian {$mapel->name} berhasil disimpan.");
    }

    /**
     * Show the grade input form.
     */
    public function input(Request $request): View
    {
        $user = $request->user();

        $academicYears = AcademicYear::orderByDesc('id')->get();
        $academicYear = $request->filled('academic_year')
            ? AcademicYear::findOrFail($request->integer('academic_year'))
            : AcademicYear::current();

        $mapels = $this->accessibleMapels($user);
        $kelasList = $this->accessibleKelas($user);

        $selectedMapel = $request->filled('mapel')
            ? $mapels->firstWhere('id', $request->integer('mapel'))
            : null;

        $selectedKelas = $request->filled('kelas')
            ? $kelasList->firstWhere('id', $request->integer('kelas'))
            : null;

        $students = collect();
        $rows = [];
        $weight = null;

        if ($selectedMapel !== null && $selectedKelas !== null) {
            $this->ensureTeacherTeachesMapel($user, $selectedMapel->id, $selectedKelas->id);

            $students = User::query()
                ->where('role', 'murid')
                ->where('kelas_id', $selectedKelas->id)
                ->orderBy('name')
                ->get();

            $weight = GradeWeight::firstOrCreate(
                [
                    'mapel_id' => $selectedMapel->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'assignment_weight' => 30,
                    'uts_weight' => 30,
                    'uas_weight' => 40,
                ]
            );

            $assignmentScores = $this->assignmentScores($selectedMapel->id, $students->pluck('id'));

            $components = GradeComponent::query()
                ->where('mapel_id', $selectedMapel->id)
                ->where('academic_year_id', $academicYear->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $finalGrades = FinalGrade::query()
                ->where('mapel_id', $selectedMapel->id)
                ->where('academic_year_id', $academicYear->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');

            $rows = $students->mapWithKeys(function (User $student) use ($assignmentScores, $components, $finalGrades, $weight) {
                $studentComponents = $components->get($student->id) ?? collect();
                $uts = $studentComponents->firstWhere('component', 'uts')?->score;
                $uas = $studentComponents->firstWhere('component', 'uas')?->score;
                $tugas = $assignmentScores->has($student->id)
                    ? round((float) $assignmentScores->get($student->id), 2)
                    : null;

                $final = $uts !== null && $uas !== null
                    ? $this->calculateFinalScore((float) ($tugas ?? 0), (float) $uts, (float) $uas, $weight)
                    : null;

                return [
                    $student->id => [
                        'tugas' => $tugas,
                        'uts' => $uts !== null ? (float) $uts : null,
                        'uas' => $uas !== null ? (float) $uas : null,
                        'final' => $final,
                        'letter' => $final !== null ? $this->gradeLetter($final) : null,
                        'notes' => $finalGrades->get($student->id)?->teacher_notes,
                    ],
                ];
            })->all();
        }

        return view('pages.nilai.input', [
            'academicYear' => $academicYear,
            'academicYears' => $academicYears,
            'mapels' => $mapels,
            'kelasList' => $kelasList,
            'selectedMapel' => $selectedMapel,
            'selectedKelas' => $selectedKelas,
            'students' => $students,
            'rows' => $rows,
            'weight' => $weight,
        ]);
    }

    /**
     * Store the component grades and recalculate final grades.
     */
    public function storeGrades(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => ['required', 'exists:mapels,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*.uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.uas' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'scores.required' => 'Tidak ada siswa yang dapat dinilai.',
            'scores.*.uts.required' => 'Nilai UTS wajib diisi untuk setiap siswa.',
            'scores.*.uas.required' => 'Nilai UAS wajib diisi untuk setiap siswa.',
        ]);

        $user = $request->user();
        $mapel = Mapel::findOrFail($validated['mapel_id']);
        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        $this->ensureTeacherTeachesMapel($user, $mapel->id, $kelas->id);

        $studentIds = User::query()
            ->where('role', 'murid')
            ->where('kelas_id', $kelas->id)
            ->whereIn('id', array_keys($validated['scores']))
            ->pluck('id');

        if ($studentIds->count() !== count($validated['scores'])) {
            throw ValidationException::withMessages([
                'scores' => 'Daftar siswa tidak sesuai dengan kelas yang dipilih.',
            ]);
        }

        $weight = GradeWeight::firstOrCreate(
            [
                'mapel_id' => $mapel->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'assignment_weight' => 30,
                'uts_weight' => 30,
                'uas_weight' => 40,
            ]
        );

        $assignmentScores = $this->assignmentScores($mapel->id, $studentIds);

        DB::transaction(function () use ($validated, $mapel, $academicYear, $weight, $assignmentScores) {
            foreach ($validated['scores'] as $studentId => $score) {
                $uts = (float) $score['uts'];
                $uas = (float) $score['uas'];
                $assignment = (float) ($assignmentScores->get((int) $studentId) ?? 0);
                $finalScore = $this->calculateFinalScore($assignment, $uts, $uas, $weight);

                GradeComponent::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'mapel_id' => $mapel->id,
                        'academic_year_id' => $academicYear->id,
                        'component' => 'uts',
                    ],
                    ['score' => $uts]
                );

                GradeComponent::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'mapel_id' => $mapel->id,
                        'academic_year_id' => $academicYear->id,
                        'component' => 'uas',
                    ],
                    ['score' => $uas]
                );

                FinalGrade::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'mapel_id' => $mapel->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'final_score' => $finalScore,
                        'grade_letter' => $this->gradeLetter($finalScore),
                        'teacher_notes' => $score['notes'] ?? null,
                    ]
                );
            }
        });

        ActivityLog::record(
            'grade.saved',
            "Nilai {$mapel->name} kelas {$kelas->name} disimpan untuk ".count($validated['scores']).' siswa',
            null
        );

        return redirect()
            ->route('nilai.input', [
                'mapel' => $mapel->id,
                'kelas' => $kelas->id,
                'academic_year' => $academicYear->id,
            ])
            ->with('success', 'Nilai berhasil disimpan dan nilai akhir sudah dihitung otomatis.');
    }

    /**
     * Display a student report card.
     */
    public function rapor(Request $request, ?User $student = null): View|RedirectResponse
    {
        $viewer = $request->user();

        if ($student === null) {
            if (! $viewer->isStudent()) {
                return redirect()->route('nilai.index')->with('error', 'Pilih siswa untuk melihat rapor.');
            }

            $student = $viewer;
        }

        $this->ensureCanViewReport($viewer, $student);

        return view('pages.nilai.rapor', $this->reportData($request, $student));
    }

    /**
     * Download a student report card as PDF.
     */
    public function raporPdf(Request $request, User $student): Response
    {
        $this->ensureCanViewReport($request->user(), $student);

        return Pdf::loadView('pages.nilai.rapor-pdf', $this->reportData($request, $student))
            ->setPaper('a4', 'portrait')
            ->download('rapor-'.$student->nis.'-'.$student->name.'.pdf');
    }

    /**
     * Collect the report card data.
     *
     * @return array<string, mixed>
     */
    private function reportData(Request $request, User $student): array
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $academicYear = $request->filled('academic_year')
            ? AcademicYear::findOrFail($request->integer('academic_year'))
            : AcademicYear::current();

        $finalGrades = FinalGrade::query()
            ->with('mapel')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->sortBy(fn (FinalGrade $grade) => $grade->mapel?->name);

        return [
            'student' => $student->loadMissing('kelas'),
            'academicYear' => $academicYear,
            'academicYears' => $academicYears,
            'finalGrades' => $finalGrades,
            'average' => $finalGrades->isEmpty() ? null : round((float) $finalGrades->avg('final_score'), 2),
            'school' => SchoolProfile::current(),
        ];
    }

    /**
     * Average score of all task submissions for a subject.
     *
     * @param  Collection<int, int>  $studentIds
     * @return Collection<int, float>
     */
    private function assignmentScores(int $mapelId, Collection $studentIds): Collection
    {
        if ($studentIds->isEmpty()) {
            return collect();
        }

        return ExamSubmission::query()
            ->join('exams', 'exams.id', '=', 'exam_submissions.exam_id')
            ->where('exams.type', 'tugas')
            ->where('exams.mapel_id', $mapelId)
            ->whereIn('exam_submissions.user_id', $studentIds)
            ->selectRaw('exam_submissions.user_id as student_id, AVG(exam_submissions.score) as average')
            ->groupBy('exam_submissions.user_id')
            ->pluck('average', 'student_id');
    }

    /**
     * Calculate the weighted final score.
     */
    private function calculateFinalScore(float $assignment, float $uts, float $uas, GradeWeight $weight): float
    {
        $score = ($assignment * $weight->assignment_weight / 100)
            + ($uts * $weight->uts_weight / 100)
            + ($uas * $weight->uas_weight / 100);

        return round($score, 2);
    }

    /**
     * Map a final score to a grade letter.
     */
    private function gradeLetter(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    /**
     * @return Collection<int, Mapel>
     */
    private function accessibleMapels(User $user): Collection
    {
        return Mapel::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->whereHas(
                'teachingAssignments',
                fn ($query) => $query->where('teacher_id', $user->id)
            ))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Kelas>
     */
    private function accessibleKelas(User $user): Collection
    {
        return Kelas::query()
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->whereHas(
                'teachingAssignments',
                fn ($query) => $query->where('teacher_id', $user->id)
            ))
            ->orderBy('name')
            ->get();
    }

    /**
     * Abort when a teacher is not assigned to the subject (and class).
     */
    private function ensureTeacherTeachesMapel(User $user, int $mapelId, ?int $kelasId = null): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $teaches = TeachingAssignment::query()
            ->where('teacher_id', $user->id)
            ->where('mapel_id', $mapelId)
            ->when($kelasId !== null, fn ($query) => $query->where('kelas_id', $kelasId))
            ->exists();

        abort_unless($teaches, 403, 'Anda tidak mengajar mata pelajaran atau kelas ini.');
    }

    /**
     * Abort when a student tries to view somebody else's report card.
     */
    private function ensureCanViewReport(User $viewer, User $student): void
    {
        if ($viewer->isStudent() && $viewer->id !== $student->id) {
            abort(403, 'Anda hanya dapat melihat rapor milik sendiri.');
        }
    }
}
