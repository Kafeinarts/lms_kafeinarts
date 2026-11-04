@extends('layouts.app')

@section('title', 'Tahun Ajaran - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Tahun Ajaran</h1>
            <p class="text-muted small mb-0">Kelola periode akademik yang dipakai untuk bobot, nilai, dan rapor</p>
        </div>
        <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-plus-circle mr-1"></i> Tambah Tahun Ajaran</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('nilai.academic-years.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="font-weight-bold text-gray-700">Nama Tahun Ajaran</label>
                            <input type="text" name="name" id="name" value="{{ old('name', '2026/2027') }}"
                                placeholder="Contoh: 2026/2027" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="semester" class="font-weight-bold text-gray-700">Semester</label>
                            <select name="semester" id="semester" class="form-control @error('semester') is-invalid @enderror" required>
                                <option value="ganjil" {{ old('semester') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" {{ old('semester') === 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                class="custom-control-input" {{ old('is_active') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Langsung aktifkan</label>
                        </div>
                        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i> Daftar Tahun Ajaran</h6>
                    <span class="badge badge-light border">Total: {{ $academicYears->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Tahun Ajaran</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Bobot</th>
                                    <th class="text-center">Nilai Akhir</th>
                                    <th style="width: 120px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($academicYears as $index => $year)
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="align-middle font-weight-bold text-gray-900">{{ $year->label }}</td>
                                        <td class="text-center align-middle">
                                            @if ($year->is_active)
                                                <span class="badge badge-success px-2 py-1">Aktif</span>
                                            @else
                                                <span class="badge badge-light border px-2 py-1">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">{{ $year->grade_weights_count }}</td>
                                        <td class="text-center align-middle">{{ $year->final_grades_count }}</td>
                                        <td class="text-center align-middle">
                                            @unless ($year->is_active)
                                                <form action="{{ route('nilai.academic-years.activate', $year) }}" method="POST" class="d-inline"
                                                    data-confirm='Aktifkan tahun ajaran {{ $year->label }}?'>
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada tahun ajaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
