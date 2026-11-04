@extends('layouts.app')

@section('title', 'Bimbingan Konseling - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Bimbingan Konseling</h1>
            <p class="text-muted small mb-0">Pantau poin pelanggaran, prestasi, dan sesi konseling siswa</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('bk.achievements.create') }}" class="btn btn-sm btn-success shadow-sm font-weight-bold mr-1">
                <i class="fas fa-trophy fa-sm text-white-50 mr-1"></i> Catat Prestasi
            </a>
            <a href="{{ route('bk.sessions.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                <i class="fas fa-comments fa-sm text-white-50 mr-1"></i> Sesi Konseling
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Poin Pelanggaran</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ (int) $violationPoints->sum('total_points') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Poin Prestasi</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ (int) $totalAchievementPoints }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Sesi Konseling</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ (int) $totalSessions }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Poin Pelanggaran Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th class="text-center">Jumlah Pelanggaran</th>
                            <th class="text-center">Total Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($violationPoints as $index => $row)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td class="align-middle font-weight-bold text-gray-900">{{ $row->student?->name ?? '-' }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-info">{{ $row->student?->kelas?->name ?? '-' }}</span>
                                </td>
                                <td class="text-center align-middle">{{ (int) $row->total_violations }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-danger px-2 py-1">{{ (int) $row->total_points }} poin</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada catatan pelanggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-trophy mr-1"></i> Prestasi Siswa</h6>
            <span class="badge badge-light border">Total: {{ $achievements->total() }} Catatan</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Prestasi</th>
                            <th class="text-center">Poin</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($achievements as $index => $achievement)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">{{ $achievements->firstItem() + $index }}</td>
                                <td class="align-middle small text-muted">{{ $achievement->achieved_at?->format('d M Y') }}</td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    {{ $achievement->student?->name ?? '-' }}
                                    <span class="badge badge-info ml-1">{{ $achievement->student?->kelas?->name ?? '-' }}</span>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold">{{ $achievement->title }}</div>
                                    @if ($achievement->description)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($achievement->description, 100) }}</div>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-success px-2 py-1">{{ $achievement->points }} poin</span>
                                </td>
                                <td class="align-middle small text-muted">{{ $achievement->recorder?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada prestasi siswa yang dicatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{ $achievements->links() }}
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments mr-1"></i> Sesi Konseling</h6>
            <span class="badge badge-light border">Total: {{ $sessions->total() }} Sesi</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Topik</th>
                            <th>Catatan</th>
                            <th>Tindak Lanjut</th>
                            <th>Konselor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $index => $session)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">{{ $sessions->firstItem() + $index }}</td>
                                <td class="align-middle small text-muted">{{ $session->session_date?->format('d M Y') }}</td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    {{ $session->student?->name ?? '-' }}
                                    <span class="badge badge-info ml-1">{{ $session->student?->kelas?->name ?? '-' }}</span>
                                </td>
                                <td class="align-middle font-weight-bold">{{ $session->topic }}</td>
                                <td class="align-middle small text-muted">{{ \Illuminate\Support\Str::limit($session->notes, 80) ?: '-' }}</td>
                                <td class="align-middle small text-muted">{{ \Illuminate\Support\Str::limit($session->follow_up, 80) ?: '-' }}</td>
                                <td class="align-middle small text-muted">{{ $session->counselor?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada sesi konseling.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
@endsection
