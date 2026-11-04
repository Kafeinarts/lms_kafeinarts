@extends('layouts.app')

@section('title', 'Input Presensi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Input Presensi</h1>
            <p class="text-muted small mb-0">Pilih jadwal dan tanggal, lalu tandai kehadiran setiap siswa</p>
        </div>
        <a href="{{ route('presensi.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    @if ($schedules->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Anda belum memiliki jadwal mengajar.
            <a href="{{ route('jadwal.index') }}" class="font-weight-bold">Lihat jadwal pengajaran</a>.
        </div>
    @else
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i> Pilih Jadwal</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('presensi.create') }}" method="GET" class="form-row align-items-end">
                    <div class="form-group col-md-6">
                        <label for="schedule" class="font-weight-bold text-gray-700">Jadwal Mengajar</label>
                        <select name="schedule" id="schedule" class="form-control" required>
                            <option value="">-- Pilih Jadwal --</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->id }}" {{ (string) request('schedule') === (string) $schedule->id ? 'selected' : '' }}>
                                    {{ $schedule->day_name }} {{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                    {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    | {{ $schedule->assignment?->mapel?->name }}
                                    ({{ $schedule->assignment?->kelas?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="date" class="font-weight-bold text-gray-700">Tanggal</label>
                        <input type="date" name="date" id="date" value="{{ $date }}" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-users fa-sm mr-1"></i> Tampilkan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($selectedSchedule)
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clipboard-check mr-1"></i> Daftar Siswa
                </h6>
                <span class="badge badge-light border">
                    {{ $selectedSchedule->assignment?->kelas?->name }} &middot; {{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}
                </span>
            </div>
            <div class="card-body">
                @if ($existing)
                    <div class="alert alert-warning">
                        <i class="fas fa-lock mr-1"></i> Presensi untuk jadwal dan tanggal ini sudah tersimpan sehingga tidak dapat diinput ulang.
                        <a href="{{ route('presensi.show', $existing) }}" class="font-weight-bold">Lihat presensi tersimpan</a>.
                    </div>
                @elseif ($students->isEmpty())
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada siswa terdaftar di kelas ini.
                    </div>
                @else
                    <form action="{{ route('presensi.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 60px;" class="text-center">No</th>
                                        <th>Nama Siswa</th>
                                        <th style="width: 260px;" class="text-center">Status Kehadiran</th>
                                        <th style="width: 260px;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $index => $student)
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-gray-900">{{ $student->name }}</div>
                                                <div class="small text-muted">NIS: {{ $student->nis ?: '-' }}</div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-center">
                                                    @foreach ($statuses as $status => $label)
                                                        <div class="custom-control custom-radio custom-control-inline">
                                                            <input type="radio" id="status-{{ $student->id }}-{{ $status }}"
                                                                name="details[{{ $student->id }}][status]" value="{{ $status }}"
                                                                class="custom-control-input"
                                                                {{ old("details.{$student->id}.status", 'H') === $status ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status-{{ $student->id }}-{{ $status }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <input type="text" name="details[{{ $student->id }}][reason]"
                                                    value="{{ old("details.{$student->id}.reason") }}"
                                                    class="form-control form-control-sm" placeholder="Opsional">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group mt-3">
                            <label for="notes" class="font-weight-bold text-gray-700">Catatan Pertemuan (opsional)</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control"
                                placeholder="Materi pertemuan, kendala kelas, dan sebagainya">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('presensi.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Presensi
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
@endsection
