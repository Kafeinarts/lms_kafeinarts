@extends('layouts.app')

@section('title', 'Pelanggaran & Pemanggilan Siswa - StaffWork')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Pelanggaran & Pemanggilan Siswa</h1>
            <p class="text-muted small mb-0">Catat pelanggaran siswa; siswa otomatis menerima notifikasi panggilan ke ruang BK/ruang guru</p>
        </div>
        <a href="{{ route('pelanggaran.create') }}" class="btn btn-sm btn-danger shadow-sm font-weight-bold">
            <i class="fas fa-exclamation-triangle fa-sm text-white-50 mr-1"></i> Catat Pelanggaran
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list mr-1"></i> Riwayat Pelanggaran
            </h6>
            <span class="badge badge-light border">Total: {{ $violations->total() }} Catatan</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Keterangan Pelanggaran</th>
                            <th style="width: 90px;" class="text-center">Poin</th>
                            <th style="width: 130px;" class="text-center">Tujuan</th>
                            <th>Dilaporkan Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($violations as $index => $violation)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">
                                    {{ $violations->firstItem() + $index }}
                                </td>
                                <td class="align-middle small text-muted">
                                    {{ $violation->created_at?->format('d M Y, H:i') }}
                                </td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    {{ $violation->student?->name ?? '-' }}
                                    @if ($violation->student?->kelas)
                                        <span class="badge badge-info ml-1">{{ $violation->student->kelas->name }}</span>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $violation->description }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light border px-2 py-1">{{ $violation->points }} poin</span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $violation->room === 'bk' ? 'badge-danger' : 'badge-warning' }} px-2 py-1">
                                        {{ $violation->room_name }}
                                    </span>
                                </td>
                                <td class="align-middle text-muted">{{ $violation->reporter?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada catatan pelanggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $violations->links() }}
            </div>
        </div>
    </div>
@endsection
