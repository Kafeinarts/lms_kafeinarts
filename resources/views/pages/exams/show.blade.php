@extends('layouts.app')

@section('title', $exam->title . ' - StaffWork LMS')

@section('content')
    @php
        $isTeacher = Auth::user()->isTeacher();
        $totalQuestions = $exam->questions->count();
    @endphp

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">{{ $exam->title }}</h1>
            <p class="text-muted small mb-0">
                <span class="badge {{ $exam->type_badge_class }}">{{ $exam->type_label }}</span>
                @if ($exam->mapel)
                    <span class="badge badge-primary">
                        <i class="fas fa-book mr-1"></i>{{ $exam->mapel->name }}
                    </span>
                @endif
                <span class="mx-1">&bull;</span>
                {{ $totalQuestions }} soal
                @if ($exam->duration_minutes)
                    <span class="mx-1">&bull;</span> {{ $exam->duration_minutes }} menit
                @endif
                <span class="mx-1">&bull;</span>
                <i class="fas fa-user mr-1"></i>{{ $exam->author->name ?? '-' }}
            </p>
        </div>
        <div>
            @if ($isTeacher)
                <a href="{{ route('exams.edit', $exam) }}" class="btn btn-sm btn-outline-primary shadow-sm mr-1">
                    <i class="fas fa-edit fa-sm mr-1"></i> Edit
                </a>
            @endif
            <a href="{{ route('exams.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
            </a>
        </div>
    </div>

    @if ($exam->description)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            {!! nl2br(e($exam->description)) !!}
        </div>
    @endif

    @if ($exam->available_at && $exam->available_at->isFuture())
        <div class="alert alert-warning">
            <i class="fas fa-clock mr-1"></i>
            Tersedia mulai {{ $exam->available_at->format('d M Y, H:i') }}.
        </div>
    @endif

    @if ($submission)
        <div class="alert alert-success d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <i class="fas fa-check-circle mr-1"></i>
                Jawaban sudah dikirim pada {{ $submission->submitted_at->format('d M Y, H:i') }}.
                Benar <strong>{{ $correctCount }}</strong> dari <strong>{{ $totalQuestions }}</strong> soal.
            </div>
            <div class="display-4 font-weight-bold ml-3">{{ $submission->score }}</div>
        </div>
    @endif

    @if (! $isTeacher && $submission === null)
        <form id="quizForm" action="{{ route('exams.submit', $exam) }}" method="POST"
            data-confirm="Kirim jawaban sekarang? Jawaban yang sudah dikirim tidak dapat diubah.">
            @csrf
        </form>
    @endif

    <div class="row">
        @forelse ($exam->questions as $i => $question)
            @php
                $chosen = $submission?->answers[(string) $question->id] ?? null;
            @endphp
            <div class="col-lg-8">
                <div class="card shadow mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <strong class="text-primary">Soal #{{ $i + 1 }}</strong>
                        @if ($isTeacher)
                            <span class="badge badge-success">
                                Jawaban: {{ $question->correct_answer }}
                            </span>
                        @elseif ($submission !== null)
                            <span class="badge {{ $chosen === null ? 'badge-secondary' : ($chosen === $question->correct_answer ? 'badge-success' : 'badge-danger') }}">
                                {{ $chosen === null ? 'Tidak dijawab' : ($chosen === $question->correct_answer ? 'Benar' : 'Salah') }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <p class="font-weight-bold text-gray-800 mb-3">{!! nl2br(e($question->question)) !!}</p>
                        @if ($isTeacher)
                            <ul class="list-unstyled mb-0">
                                @foreach ($question->options as $letter => $text)
                                    <li class="mb-1">
                                        <span class="badge badge-light border mr-2">{{ $letter }}</span>
                                        {{ $text }}
                                    </li>
                                @endforeach
                            </ul>
                        @elseif ($submission !== null)
                            <div class="quiz-options">
                                @foreach ($question->options as $letter => $text)
                                    <div class="quiz-option is-locked {{ $letter === $question->correct_answer ? 'is-correct' : '' }} {{ $letter === $chosen && $letter !== $question->correct_answer ? 'is-wrong' : '' }}">
                                        <span class="quiz-letter">{{ $letter }}</span>
                                        <span class="quiz-text">{{ $text }}</span>
                                        @if ($letter === $chosen)
                                            <span class="badge badge-info ml-auto">Pilihan Anda</span>
                                        @endif
                                    </div>
                                @endforeach
                                @if ($chosen === null)
                                    <div class="small text-muted mt-1"><em>Tidak dijawab.</em></div>
                                @endif
                            </div>
                        @else
                            <div class="quiz-options" data-question-id="{{ $question->id }}">
                                @foreach ($question->options as $letter => $text)
                                    <label class="quiz-option">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $letter }}">
                                        <span class="quiz-letter">{{ $letter }}</span>
                                        <span class="quiz-text">{{ $text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-clipboard fa-3x text-gray-300 mb-3"></i>
                        <p class="mb-0">Belum ada soal pada tugas/ujian ini.</p>
                    </div>
                </div>
            </div>
        @endforelse

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-list mr-1"></i> Ringkasan
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-0">
                        <tr>
                            <th class="bg-light">Jenis</th>
                            <td><span class="badge {{ $exam->type_badge_class }}">{{ $exam->type_label }}</span></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Mata Pelajaran</th>
                            <td>
                                @if ($exam->mapel)
                                    <span class="badge badge-primary">{{ $exam->mapel->name }}</span>
                                @else
                                    <span class="text-muted small">Belum ditentukan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Jumlah Soal</th>
                            <td>{{ $totalQuestions }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Durasi</th>
                            <td>{{ $exam->duration_minutes ? $exam->duration_minutes . ' menit' : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Dibuat</th>
                            <td>{{ $exam->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>

                    @if (! $isTeacher && $submission === null && $totalQuestions > 0)
                        <hr>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Terjawab</span>
                            <strong><span id="answeredCount">0</span>/{{ $totalQuestions }}</strong>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" id="answeredProgress" style="width: 0%;"></div>
                        </div>
                        @error('answers')
                            <div class="alert alert-danger small py-2">{{ $message }}</div>
                        @enderror
                        <button type="submit" form="quizForm"
                            class="btn btn-success btn-block font-weight-bold" id="finishQuiz">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Jawaban
                        </button>
                        <small class="form-text text-muted text-center mt-2 mb-0">
                            Pilih satu jawaban pada setiap soal, lalu tekan tombol di atas.
                        </small>
                    @elseif (! $isTeacher && $submission !== null)
                        <hr>
                        <div class="text-center py-2">
                            <div class="display-4 font-weight-bold text-success">{{ $submission->score }}</div>
                            <div class="text-muted small">Nilai Akhir</div>
                            <div class="mt-2 small">
                                Benar <strong>{{ $correctCount }}</strong> /
                                <strong>{{ $totalQuestions }}</strong> soal
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($isTeacher)
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-inbox mr-1"></i> Pengumpulan Jawaban
                </h6>
                <span class="badge badge-light border">Total: {{ $submissions->count() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">No</th>
                                <th>Siswa</th>
                                <th style="width: 160px;">Waktu Kirim</th>
                                <th style="width: 100px;" class="text-center">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($submissions as $index => $studentSubmission)
                                <tr>
                                    <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                    <td>{{ $studentSubmission->student->name ?? '-' }}</td>
                                    <td class="small text-muted">
                                        {{ $studentSubmission->submitted_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $studentSubmission->score >= 70 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $studentSubmission->score }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada siswa yang mengirim jawaban.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var questionGroups = Array.prototype.slice.call(document.querySelectorAll('.quiz-options'));
        var finishButton = document.getElementById('finishQuiz');

        if (!finishButton || questionGroups.length === 0) {
            return;
        }

        var answeredCount = document.getElementById('answeredCount');
        var answeredProgress = document.getElementById('answeredProgress');

        function updateProgress() {
            var answered = questionGroups.filter(function (group) {
                return group.querySelector('input:checked') !== null;
            }).length;

            answeredCount.textContent = answered;
            answeredProgress.style.width = Math.round((answered / questionGroups.length) * 100) + '%';

            return answered;
        }

        questionGroups.forEach(function (group) {
            group.querySelectorAll('input[type="radio"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    group.querySelectorAll('.quiz-option').forEach(function (option) {
                        option.classList.remove('is-selected');
                    });

                    input.closest('.quiz-option').classList.add('is-selected');
                    updateProgress();
                });
            });
        });

        updateProgress();
    });
</script>
@endpush
