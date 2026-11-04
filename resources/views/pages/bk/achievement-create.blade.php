@extends('layouts.app')

@section('title', 'Catat Prestasi Siswa - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Catat Prestasi Siswa</h1>
            <p class="text-muted small mb-0">Dokumentasikan prestasi akademik maupun non-akademik siswa</p>
        </div>
        <a href="{{ route('bk.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-trophy mr-1"></i> Formulir Prestasi</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('bk.achievements.store') }}" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label for="student_id" class="font-weight-bold text-gray-700">Siswa <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('student_id') ? '' : 'selected' }}>-- Pilih Siswa --</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" {{ (string) old('student_id') === (string) $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->kelas?->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-5">
                                <label for="achieved_at" class="font-weight-bold text-gray-700">Tanggal Prestasi <span class="text-danger">*</span></label>
                                <input type="date" name="achieved_at" id="achieved_at"
                                    value="{{ old('achieved_at', now()->toDateString()) }}"
                                    class="form-control @error('achieved_at') is-invalid @enderror" required>
                                @error('achieved_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="title" class="font-weight-bold text-gray-700">Nama Prestasi <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Contoh: Juara 1 Olimpiade Matematika Tingkat Kota" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="points" class="font-weight-bold text-gray-700">Poin Prestasi</label>
                                <input type="number" name="points" id="points" value="{{ old('points', 10) }}"
                                    min="0" max="1000" class="form-control @error('points') is-invalid @enderror">
                                @error('points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-8">
                                <label for="description" class="font-weight-bold text-gray-700">Deskripsi</label>
                                <textarea name="description" id="description" rows="2"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Keterangan tambahan mengenai prestasi...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('bk.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Prestasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
