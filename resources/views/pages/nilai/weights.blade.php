@extends('layouts.app')

@section('title', 'Bobot Penilaian - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Bobot Penilaian</h1>
            <p class="text-muted small mb-0">{{ $mapel->name }} &middot; {{ $academicYear->label }}</p>
        </div>
        <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-balance-scale mr-1"></i> Konfigurasi Bobot</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('nilai.weights') }}" method="GET" class="form-group">
                        <label for="academic_year" class="font-weight-bold text-gray-700">Tahun Ajaran</label>
                        <select name="academic_year" id="academic_year" class="form-control" onchange="this.form.submit()">
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $year->id === $academicYear->id ? 'selected' : '' }}>
                                    {{ $year->label }}{{ $year->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="mapel" value="{{ $mapel->id }}">
                        <noscript><button type="submit" class="btn btn-sm btn-secondary mt-2">Ganti Tahun Ajaran</button></noscript>
                    </form>

                    <hr>

                    <form action="{{ route('nilai.weights.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
                        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="assignment_weight" class="font-weight-bold text-gray-700">Bobot Tugas (%)</label>
                                <input type="number" name="assignment_weight" id="assignment_weight"
                                    value="{{ old('assignment_weight', $weight?->assignment_weight ?? 30) }}"
                                    min="0" max="100" class="form-control @error('assignment_weight') is-invalid @enderror" required>
                                @error('assignment_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="uts_weight" class="font-weight-bold text-gray-700">Bobot UTS (%)</label>
                                <input type="number" name="uts_weight" id="uts_weight"
                                    value="{{ old('uts_weight', $weight?->uts_weight ?? 30) }}"
                                    min="0" max="100" class="form-control @error('uts_weight') is-invalid @enderror" required>
                                @error('uts_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="uas_weight" class="font-weight-bold text-gray-700">Bobot UAS (%)</label>
                                <input type="number" name="uas_weight" id="uas_weight"
                                    value="{{ old('uas_weight', $weight?->uas_weight ?? 40) }}"
                                    min="0" max="100" class="form-control @error('uas_weight') is-invalid @enderror" required>
                                @error('uas_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <p class="small text-muted">
                            <i class="fas fa-info-circle mr-1"></i> Total ketiga bobot harus tepat 100%.
                        </p>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('nilai.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Bobot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
