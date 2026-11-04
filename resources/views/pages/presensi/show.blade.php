@extends('layouts.app')

@section('title', 'Detail Presensi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Detail Presensi</h1>
            <p class="text-muted small mb-0">
                {{ $attendance->schedule?->assignment?->mapel?->name ?? '-' }}
                &middot; {{ $attendance->schedule?->assignment?->kelas?->name ?? '-' }}
                &middot; {{ $attendance->date?->format('d M Y') }}
            </p>
        </div>
        <div class="d-flex align-items-center">
            @if (Auth::user()->isTeacher())
                <a href="{{ route('presensi.recap') }}" class="btn btn-sm btn-info shadow-sm mr-1">
                    <i class="fas fa-chart-bar fa-sm text-white-50 mr-1"></i> Rekap
                </a>
            @endif
            <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle mr-1"></i> Informasi Pertemuan</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-gray-700">Mata Pelajaran</dt>
                        <dd class="col-7 text-gray-900">{{ $attendance->schedule?->assignment?->mapel?->name ?? '-' }}</dd>

                        <dt class="col-5 text-gray-700">Kelas</dt>
                        <dd class="col-7 text-gray-900">{{ $attendance->schedule?->assignment?->kelas?->name ?? '-' }}</dd>

                        <dt class="col-5 text-gray-700">Hari / Jam</dt>
                        <dd class="col-7 text-gray-900">
                            {{ $attendance->schedule?->day_name ?? '-' }},
                            {{ $attendance->schedule?->start_time ? \Illuminate\Support\Carbon::parse($attendance->schedule->start_time)->format('H:i') : '-' }}
                            -
                            {{ $attendance->schedule?->end_time ? \Illuminate\Support\Carbon::parse($attendance->schedule->end_time)->format('H:i') : '-' }}
                        </dd>

                        <dt class="col-5 text-gray-700">Tanggal</dt>
                        <dd class="col-7 text-gray-900">{{ $attendance->date?->format('d M Y') }}</dd>

                        <dt class="col-5 text-gray-700">Diinput Oleh</dt>
                        <dd class="col-7 text-gray-900">{{ $attendance->teacher?->name ?? '-' }}</dd>
                    </dl>

                    @if ($attendance->notes)
                        <hr>
                        <div class="small font-weight-bold text-gray-700 mb-1">Catatan Pertemuan</div>
                        <p class="small text-muted mb-0">{{ $attendance->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-1"></i> Kehadiran Siswa</h6>
                    @php $summary = $attendance->status_summary; @endphp
                    <div>
                        <span class="badge badge-success">H: {{ $summary['H'] }}</span>
                        <span class="badge badge-info">I: {{ $summary['I'] }}</span>
                        <span class="badge badge-warning">S: {{ $summary['S'] }}</span>
                        <span class="badge badge-danger">A: {{ $summary['A'] }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th style="width: 130px;" class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendance->details as $index => $detail)
                                    @continue($ownDetail !== null && $detail->id !== $ownDetail->id)
                                    <tr>
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="align-middle font-weight-bold text-gray-900">
                                            {{ $detail->student?->name ?? '-' }}
                                            <div class="small text-muted font-weight-normal">NIS: {{ $detail->student?->nis ?: '-' }}</div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge {{ $detail->status_badge_class }} px-2 py-1">{{ $detail->status_label }}</span>
                                        </td>
                                        <td class="align-middle small text-muted">{{ $detail->reason ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada data kehadiran.</td>
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
