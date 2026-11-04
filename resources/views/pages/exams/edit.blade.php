@extends('layouts.app')

@section('title', 'Edit Tugas/Ujian - StaffWork LMS')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Edit Tugas / Ujian</h1>
            <p class="text-muted small mb-0">{{ $exam->title }}</p>
        </div>
        <a href="{{ route('exams.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <form action="{{ route('exams.update', $exam) }}" method="POST" id="exam-form">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle mr-1"></i> Info Tugas/Ujian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title" class="font-weight-bold text-gray-700">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $exam->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mapel_id" class="font-weight-bold text-gray-700">Mata Pelajaran</label>
                            <select name="mapel_id" id="mapel_id" class="form-control @error('mapel_id') is-invalid @enderror">
                                <option value="">— Tanpa Mapel —</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}" {{ (string) old('mapel_id', $exam->mapel_id) === (string) $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="type" class="font-weight-bold text-gray-700">Jenis <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="tugas" {{ old('type', $exam->type) === 'tugas' ? 'selected' : '' }}>Tugas</option>
                                <option value="ujian" {{ old('type', $exam->type) === 'ujian' ? 'selected' : '' }}>Ujian</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="font-weight-bold text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror">{{ old('description', $exam->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="duration_minutes" class="font-weight-bold text-gray-700">Durasi (Menit)</label>
                                <input type="number" name="duration_minutes" id="duration_minutes"
                                    class="form-control @error('duration_minutes') is-invalid @enderror"
                                    value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1" max="600">
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="available_at" class="font-weight-bold text-gray-700">Tersedia Pada</label>
                                <input type="datetime-local" name="available_at" id="available_at"
                                    class="form-control @error('available_at') is-invalid @enderror"
                                    value="{{ old('available_at', optional($exam->available_at)->format('Y-m-d\TH:i')) }}">
                                @error('available_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list-ol mr-1"></i> Soal Pilihan Ganda
                        </h6>
                        <button type="button" class="btn btn-sm btn-success font-weight-bold" id="add-question">
                            <i class="fas fa-plus fa-sm mr-1"></i> Tambah Soal
                        </button>
                    </div>
                    <div class="card-body">
                        @error('questions')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div id="questions-container">
                            @if (old('questions'))
                                @foreach (old('questions') as $i => $q)
                                    @include('pages.exams.partials.question-fields', ['index' => $i, 'question' => $q])
                                @endforeach
                            @else
                                @foreach ($exam->questions as $i => $q)
                                    @include('pages.exams.partials.question-fields', ['index' => $i, 'question' => $q->toArray()])
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    let questionIndex = {{ old('questions') ? count(old('questions')) : ($exam->questions->count() ?: 1) }};

    $('#add-question').on('click', function() {
        const container = $('#questions-container');
        const template = $('#question-template').html().replace(/__INDEX__/g, questionIndex);
        const $el = $(template).removeClass('d-none');
        container.append($el);
        $el.find('.question-number').text('Soal #' + (questionIndex + 1));
        questionIndex++;
    });

    $(document).on('click', '.remove-question', function() {
        if ($('#questions-container .question-card').length <= 1) {
            alert('Minimal harus ada 1 soal.');
            return;
        }
        $(this).closest('.question-card').remove();
    });
</script>

<script type="text/html" id="question-template">
    @include('pages.exams.partials.question-fields', ['index' => '__INDEX__', 'question' => null, 'isTemplate' => true])
</script>
@endpush
