@extends('layouts.app')

@section('title', 'Buat Topik Diskusi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Buat Topik Diskusi</h1>
            <p class="text-muted small mb-0">Mulai diskusi baru untuk mata pelajaran tertentu</p>
        </div>
        <a href="{{ route('forum.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-1"></i> Formulir Topik</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('forum.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="mapel_id" class="font-weight-bold text-gray-700">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" id="mapel_id" class="form-control @error('mapel_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('mapel_id') ? '' : 'selected' }}>-- Pilih Mapel --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}" {{ (string) old('mapel_id') === (string) $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="title" class="font-weight-bold text-gray-700">Judul Topik <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Contoh: Cara menyelesaikan persamaan kuadrat" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="body" class="font-weight-bold text-gray-700">Isi Diskusi <span class="text-danger">*</span></label>
                            <textarea name="body" id="body" rows="7"
                                class="form-control @error('body') is-invalid @enderror"
                                placeholder="Tulis pertanyaan atau pendapat Anda..." required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('forum.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-paper-plane mr-1"></i> Publikasikan Topik
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
