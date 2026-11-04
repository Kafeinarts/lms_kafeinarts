<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Violation;
use App\Notifications\StudentCalledNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations.
     */
    public function index(): View
    {
        $violations = Violation::with(['student.kelas', 'reporter'])
            ->latest()
            ->paginate(15);

        return view('pages.pelanggaran.index', [
            'violations' => $violations,
        ]);
    }

    /**
     * Show the form for reporting a violation.
     */
    public function create(): View
    {
        return view('pages.pelanggaran.create', [
            'students' => User::where('role', 'murid')->with('kelas')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a violation and notify the student.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'description' => ['required', 'string', 'max:1000'],
            'points' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'room' => ['required', 'string', 'in:bk,guru'],
        ], [
            'student_id.required' => 'Siswa wajib dipilih.',
            'room.in' => 'Tujuan ruang tidak valid.',
        ]);

        $student = User::where('id', $validated['student_id'])->where('role', 'murid')->first();
        if ($student === null) {
            return back()->withErrors(['student_id' => 'Pengguna yang dipilih bukan murid.'])->withInput();
        }

        $violation = Violation::create([
            ...$validated,
            'points' => $validated['points'] ?? 0,
            'reported_by' => $request->user()->id,
        ]);

        $student->notify(new StudentCalledNotification($violation));

        ActivityLog::record('violation.created', "Pelanggaran siswa '{$student->name}' dicatat ({$violation->room_name})", $violation);

        $roomName = $violation->room_name;

        return redirect()->route('pelanggaran.index')->with('success', "Pelanggaran dicatat. Siswa '{$student->name}' dipanggil ke {$roomName}.");
    }
}
