@extends('layouts.app')

@section('title', 'Rekap Presensi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Rekap Presensi</h1>
            <p class="text-muted small mb-0">Rekap kehadiran siswa per bulan untuk evaluasi kedisiplinan</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('presensi.recap.export', request()->query()) }}" class="btn btn-sm btn-success shadow-sm font-weight-bold mr-1">
                <i class="fas fa-file-excel fa-sm text-white-50 mr-1"></i> Export Excel
            </a>
            <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('presensi.recap') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-md-4 mb-md-0">
                    <label for="month" class="small font-weight-bold text-gray-700">Bulan</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-4 mb-md-0">
                    <label for="kelas" class="small font-weight-bold text-gray-700">Kelas</label>
                    <select name="kelas" id="kelas" class="form-control form-control-sm">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) request('kelas') === (string) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 mb-0">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter fa-sm mr-1"></i> Tampilkan Rekap
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Alfa</th>
                            <th style="width: 170px;">Persentase Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $index => $student)
                            @php
                                $statuses = $recap[$student->id] ?? [];
                                $present = (int) ($statuses['H'] ?? 0);
                                $total = array_sum($statuses);
                                $percentage = $total > 0 ? (int) round($present / $total * 100) : 0;
                            @endphp
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td class="align-middle small">{{ $student->nis ?: '-' }}</td>
                                <td class="align-middle font-weight-bold text-gray-900">{{ $student->name }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-info px-2 py-1">{{ $student->kelas?->name ?? '-' }}</span>
                                </td>
                                <td class="text-center align-middle">{{ $present }}</td>
                                <td class="text-center align-middle">{{ (int) ($statuses['I'] ?? 0) }}</td>
                                <td class="text-center align-middle">{{ (int) ($statuses['S'] ?? 0) }}</td>
                                <td class="text-center align-middle">{{ (int) ($statuses['A'] ?? 0) }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="progress progress-sm flex-grow-1 mr-2">
                                            <div class="progress-bar {{ $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="small font-weight-bold text-gray-700">{{ $percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada data siswa atau presensi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mt-3 mb-0">
                <i class="fas fa-info-circle mr-1"></i> Persentase dihitung dari jumlah kehadiran dibagi total pertemuan tercatat pada bulan yang dipilih.
            </p>
        </div>
    </div>
@endsection
