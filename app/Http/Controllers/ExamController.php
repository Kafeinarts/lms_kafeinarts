<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Mapel;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    /**
     * Display a listing of exams (tugas & ujian).
     */
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $type = in_array($type, ['tugas', 'ujian'], true) ? $type : 'tugas';
        $query = Exam::withCount('questions')->with(['author', 'mapel'])
            ->where('type', $type)
            ->latest();
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }
        if ($request->filled('mapel')) {
            $query->where('mapel_id', $request->integer('mapel'));
        }
        if ($request->filled('guru')) {
            $query->where('user_id', $request->integer('guru'));
        }
        $exams = $query->paginate(10)->withQueryString();

        $selectedMapel = $request->filled('mapel')
            ? Mapel::find($request->integer('mapel'))
            : null;

        return view('pages.exams.index', [
            'exams' => $exams,
            'type' => $type,
            'mapels' => Mapel::orderBy('name')->get(),
            'teachers' => $selectedMapel?->assignedTeachers ?? collect(),
        ]);
    }

    /**
     * Show the form for creating a new exam with questions.
     */
    public function create(): View
    {
        return view('pages.exams.create', [
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created exam with its multiple-choice questions.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['tugas', 'ujian'])],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'available_at' => ['nullable', 'date'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string', 'max:500'],
            'questions.*.option_b' => ['required', 'string', 'max:500'],
            'questions.*.option_c' => ['required', 'string', 'max:500'],
            'questions.*.option_d' => ['required', 'string', 'max:500'],
            'questions.*.correct_answer' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
        ], [
            'questions.min' => 'Minimal harus ada 1 soal.',
            'questions.*.correct_answer.in' => 'Jawaban benar harus salah satu dari A, B, C, atau D.',
        ]);

        $exam = DB::transaction(function () use ($request, $validated) {
            $exam = Exam::create([
                'user_id' => $request->user()->id,
                'mapel_id' => $validated['mapel_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'available_at' => $validated['available_at'] ?? now(),
            ]);

            foreach ($validated['questions'] as $index => $question) {
                Question::create([
                    'exam_id' => $exam->id,
                    'question' => $question['question'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_answer' => $question['correct_answer'],
                    'order' => $index + 1,
                ]);
            }

            return $exam;
        });

        ActivityLog::record('exam.created', "{$exam->type_label} '{$exam->title}' dibuat dengan {$exam->questions()->count()} soal", $exam);

        return redirect()->route('exams.index')->with('success', 'Tugas/Ujian beserta soal berhasil disimpan!');
    }

    /**
     * Display the specified exam with questions.
     */
    public function show(Request $request, Exam $exam): View
    {
        $exam->load(['author', 'mapel', 'questions']);

        $user = $request->user();
        $isTeacher = $user->isTeacher();
        $submission = $isTeacher ? null : $exam->submissionFor($user);

        $correctCount = $submission === null ? null : $exam->questions->sum(
            fn (Question $question) => ($submission->answers[(string) $question->id] ?? null) === $question->correct_answer ? 1 : 0
        );

        return view('pages.exams.show', [
            'exam' => $exam,
            'submission' => $submission,
            'correctCount' => $correctCount,
            'submissions' => $isTeacher ? $exam->submissions : collect(),
        ]);
    }

    /**
     * Submit the student's answers for the specified exam (auto-scored).
     */
    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isStudent(), 403, 'Hanya siswa yang dapat mengerjakan tugas/ujian ini.');

        if ($exam->submissionFor($user) !== null) {
            return redirect()->route('exams.show', $exam)
                ->with('error', 'Anda sudah pernah mengirim jawaban untuk tugas/ujian ini.');
        }
        $questions = $exam->questions;
        abort_if($questions->isEmpty(), 403, 'Tugas/ujian ini belum memiliki soal.');
        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['string', Rule::in(['A', 'B', 'C', 'D'])],
        ], [
            'answers.required' => 'Jawaban wajib diisi.',
            'answers.min' => 'Pilih minimal satu jawaban sebelum mengirim.',
            'answers.*.in' => 'Jawaban harus salah satu dari A, B, C, atau D.',
        ]);
        $answers = $validated['answers'];
        $correctCount = 0;

        foreach ($questions as $question) {
            if (($answers[(string) $question->id] ?? null) === $question->correct_answer) {
                $correctCount++;
            }
        }
        $score = (int) round($correctCount / $questions->count() * 100);
        ExamSubmission::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'answers' => $answers,
            'score' => $score,
            'submitted_at' => now(),
        ]);
        ActivityLog::record(
            'exam.submitted',
            "{$exam->type_label} '{$exam->title}' dikirim oleh {$user->name} — nilai {$score}",
            $exam
        );
        return redirect()->route('exams.show', $exam)
            ->with('success', "Jawaban berhasil dikirim! Nilai Anda: {$score}");
    }
    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam): View
    {
        $exam->load('questions');

        return view('pages.exams.edit', [
            'exam' => $exam,
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }
    /**
     * Update the specified exam and its questions.
     */
    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['tugas', 'ujian'])],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'available_at' => ['nullable', 'date'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string', 'max:500'],
            'questions.*.option_b' => ['required', 'string', 'max:500'],
            'questions.*.option_c' => ['required', 'string', 'max:500'],
            'questions.*.option_d' => ['required', 'string', 'max:500'],
            'questions.*.correct_answer' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
        ], [
            'questions.min' => 'Minimal harus ada 1 soal.',
            'questions.*.correct_answer.in' => 'Jawaban benar harus salah satu dari A, B, C, atau D.',
        ]);
        DB::transaction(function () use ($exam, $validated) {
            $exam->update([
                'title' => $validated['title'],
                'mapel_id' => $validated['mapel_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'available_at' => $validated['available_at'] ?? $exam->available_at,
            ]);
            $exam->questions()->delete();
            foreach ($validated['questions'] as $index => $question) {
                Question::create([
                    'exam_id' => $exam->id,
                    'question' => $question['question'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_answer' => $question['correct_answer'],
                    'order' => $index + 1,
                ]);
            }
        });
        ActivityLog::record('exam.updated', "{$exam->type_label} '{$exam->title}' diperbarui", $exam);
        return redirect()->route('exams.index')->with('success', 'Tugas/Ujian berhasil diperbarui!');
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam): RedirectResponse
    {
        ActivityLog::record('exam.deleted', "{$exam->type_label} '{$exam->title}' dihapus", null);
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Tugas/Ujian berhasil dihapus.');
    }
}
