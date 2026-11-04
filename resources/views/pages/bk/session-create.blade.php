@extends('layouts.app')

@section('title', 'Catat Sesi Konseling - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Catat Sesi Konseling</h1>
            <p class="text-muted small mb-0">Simpan hasil pendampingan siswa secara rahasia dan terorganisir</p>
        </div>
        <a href="{{ route('bk.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-1"></i> Formulir Sesi Konseling</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('bk.sessions.store') }}" method="POST">
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
                                <label for="session_date" class="font-weight-bold text-gray-700">Tanggal Sesi <span class="text-danger">*</span></label>
                                <input type="date" name="session_date" id="session_date"
                                    value="{{ old('session_date', now()->toDateString()) }}"
                                    class="form-control @error('session_date') is-invalid @enderror" required>
                                @error('session_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="topic" class="font-weight-bold text-gray-700">Topik Konseling <span class="text-danger">*</span></label>
                            <input type="text" name="topic" id="topic" value="{{ old('topic') }}"
                                class="form-control @error('topic') is-invalid @enderror"
                                placeholder="Contoh: Motivasi belajar dan manajemen waktu" required>
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes" class="font-weight-bold text-gray-700">Catatan Konseling</label>
                            <textarea name="notes" id="notes" rows="4"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Ringkasan permasalahan dan hasil sesi...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="follow_up" class="font-weight-bold text-gray-700">Tindak Lanjut</label>
                            <textarea name="follow_up" id="follow_up" rows="2"
                                class="form-control @error('follow_up') is-invalid @enderror"
                                placeholder="Rencana pendampingan berikutnya...">{{ old('follow_up') }}</textarea>
                            @error('follow_up')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('bk.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
