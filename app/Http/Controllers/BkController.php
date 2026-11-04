<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\ActivityLog;
use App\Models\CounselingSession;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BkController extends Controller
{
    /**
     * Display the guidance counseling dashboard.
     */
    public function index(): View
    {
        $violationPoints = Violation::query()
            ->selectRaw('student_id, SUM(points) as total_points, COUNT(*) as total_violations')
            ->with('student.kelas')
            ->groupBy('student_id')
            ->orderByDesc('total_points')
            ->get();

        $achievements = Achievement::query()
            ->with(['student.kelas', 'recorder'])
            ->latest('achieved_at')
            ->latest('id')
            ->paginate(10, ['*'], 'achievement_page');

        $sessions = CounselingSession::query()
            ->with(['student.kelas', 'counselor'])
            ->latest('session_date')
            ->latest('id')
            ->paginate(10, ['*'], 'session_page');

        return view('pages.bk.index', [
            'violationPoints' => $violationPoints,
            'achievements' => $achievements,
            'sessions' => $sessions,
            'totalAchievementPoints' => Achievement::sum('points'),
            'totalSessions' => CounselingSession::count(),
        ]);
    }

    /**
     * Show the counseling session form.
     */
    public function createSession(): View
    {
        return view('pages.bk.session-create', [
            'students' => $this->students(),
        ]);
    }

    /**
     * Store a counseling session.
     */
    public function storeSession(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'session_date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->ensureStudent($validated['student_id']);

        $session = CounselingSession::create([
            'student_id' => $validated['student_id'],
            'counselor_id' => $request->user()->id,
            'session_date' => $validated['session_date'],
            'topic' => $validated['topic'],
            'notes' => $validated['notes'] ?? null,
            'follow_up' => $validated['follow_up'] ?? null,
        ]);

        ActivityLog::record(
            'counseling.created',
            "Sesi konseling '{$session->topic}' dengan {$session->student?->name} dicatat",
            $session
        );

        return redirect()->route('bk.index')->with('success', 'Sesi konseling berhasil dicatat.');
    }

    /**
     * Show the achievement form.
     */
    public function createAchievement(): View
    {
        return view('pages.bk.achievement-create', [
            'students' => $this->students(),
        ]);
    }

    /**
     * Store a student achievement.
     */
    public function storeAchievement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'points' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'achieved_at' => ['required', 'date'],
        ]);

        $this->ensureStudent($validated['student_id']);

        $achievement = Achievement::create([
            'student_id' => $validated['student_id'],
            'recorded_by' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'points' => $validated['points'] ?? 0,
            'achieved_at' => $validated['achieved_at'],
        ]);

        ActivityLog::record(
            'achievement.created',
            "Prestasi '{$achievement->title}' untuk {$achievement->student?->name} dicatat",
            $achievement
        );

        return redirect()->route('bk.index')->with('success', 'Prestasi siswa berhasil dicatat.');
    }

    /**
     * @return Collection<int, User>
     */
    private function students(): Collection
    {
        return User::query()
            ->where('role', 'murid')
            ->with('kelas')
            ->orderBy('name')
            ->get();
    }

    /**
     * Ensure the selected user is a student.
     */
    private function ensureStudent(int $studentId): void
    {
        $isStudent = User::query()
            ->whereKey($studentId)
            ->where('role', 'murid')
            ->exists();

        if (! $isStudent) {
            throw ValidationException::withMessages([
                'student_id' => 'Pengguna yang dipilih bukan murid.',
            ]);
        }
    }
}
