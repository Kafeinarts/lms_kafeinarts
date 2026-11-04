@extends('layouts.app')

@section('title', 'Presensi - StaffWork')

@section('content')
    @if ($mode === 'student')
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Presensi Saya</h1>
                <p class="text-muted small mb-0">Riwayat kehadiran Anda pada setiap pertemuan</p>
            </div>
        </div>

        <div class="row">
            @foreach (['H' => ['Hadir', 'success'], 'I' => ['Izin', 'info'], 'S' => ['Sakit', 'warning'], 'A' => ['Alfa', 'danger']] as $status => [$label, $color])
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-{{ $color }} shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-{{ $color }} text-uppercase mb-1">{{ $label }}</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ (int) $summary->get($status, 0) }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Persentase Hadir</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $presentPercentage }}%</div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $presentPercentage }}%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percent fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-1"></i> Riwayat Kehadiran
                </h6>
                <span class="badge badge-light border">Total: {{ $details->total() }} Pertemuan</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($details as $index => $detail)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $details->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle small text-muted">
                                        {{ $detail->attendance?->date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="align-middle font-weight-bold text-gray-900">
                                        {{ $detail->attendance?->schedule?->assignment?->mapel?->name ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1">
                                            {{ $detail->attendance?->schedule?->assignment?->kelas?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge {{ $detail->status_badge_class }} px-2 py-1">{{ $detail->status_label }}</span>
                                    </td>
                                    <td class="align-middle small text-muted">{{ $detail->reason ?: '-' }}</td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('presensi.show', $detail->attendance_id) }}" class="btn btn-sm btn-outline-info" title="Lihat detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat presensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $details->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Presensi Siswa</h1>
                <p class="text-muted small mb-0">Catat kehadiran per pertemuan dan pantau rekap kehadiran kelas</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('presensi.recap') }}" class="btn btn-sm btn-info shadow-sm font-weight-bold mr-1">
                    <i class="fas fa-chart-bar fa-sm text-white-50 mr-1"></i> Rekap Presensi
                </a>
                <a href="{{ route('presensi.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Input Presensi
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <form action="{{ route('presensi.index') }}" method="GET" class="form-inline">
                    <label class="small font-weight-bold text-gray-700 mr-2" for="kelas">Filter Kelas</label>
                    <select name="kelas" id="kelas" class="form-control form-control-sm mr-2">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) request('kelas') === (string) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter fa-sm mr-1"></i> Terapkan
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Jadwal</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th class="text-center">H / I / S / A</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $index => $attendance)
                                @php $summary = $attendance->status_summary; @endphp
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        {{ $attendances->firstItem() + $index }}
                                    </td>
                                    <td class="align-middle small text-muted">
                                        {{ $attendance->date?->format('d M Y') }}
                                    </td>
                                    <td class="align-middle small">
                                        {{ $attendance->schedule?->day_name ?? '-' }},
                                        {{ $attendance->schedule?->start_time ? \Illuminate\Support\Carbon::parse($attendance->schedule->start_time)->format('H:i') : '-' }}
                                    </td>
                                    <td class="align-middle font-weight-bold text-gray-900">
                                        {{ $attendance->schedule?->assignment?->mapel?->name ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1">
                                            {{ $attendance->schedule?->assignment?->kelas?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle small">
                                        <span class="badge badge-success">{{ $summary['H'] }}</span>
                                        <span class="badge badge-info">{{ $summary['I'] }}</span>
                                        <span class="badge badge-warning">{{ $summary['S'] }}</span>
                                        <span class="badge badge-danger">{{ $summary['A'] }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('presensi.show', $attendance) }}" class="btn btn-sm btn-outline-info" title="Lihat detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada presensi yang dicatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    @endif
@endsection
