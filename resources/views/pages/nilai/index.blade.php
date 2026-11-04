@extends('layouts.app')

@section('title', 'Nilai & Rapor - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Nilai &amp; Rapor</h1>
            <p class="text-muted small mb-0">
                Konfigurasi bobot penilaian lalu input nilai tugas, UTS, dan UAS siswa
            </p>
        </div>
        <div class="d-flex align-items-center">
            @if (Auth::user()->isSuperAdmin())
                <a href="{{ route('nilai.academic-years') }}" class="btn btn-sm btn-outline-primary shadow-sm mr-1">
                    <i class="fas fa-calendar-alt fa-sm mr-1"></i> Tahun Ajaran
                </a>
            @endif
            <a href="{{ route('nilai.input') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                <i class="fas fa-edit fa-sm text-white-50 mr-1"></i> Input Nilai
            </a>
        </div>
    </div>

    <div class="alert alert-info d-flex align-items-center">
        <i class="fas fa-calculator fa-lg mr-3"></i>
        <div class="small">
            <strong>Nilai Akhir = (Nilai Tugas &times; Bobot Tugas) + (Nilai UTS &times; Bobot UTS) + (Nilai UAS &times; Bobot UAS)</strong>.
            Nilai tugas diambil otomatis dari rata-rata nilai tugas pada modul Tugas &amp; Ujian.
            Tahun ajaran aktif saat ini: <span class="badge badge-primary">{{ $academicYear->label }}</span>
        </div>
    </div>

    <div class="row">
        @forelse ($mapels as $mapel)
            @php
                $weight = $weights->get($mapel->id);
                $mapelAssignments = $assignments->get($mapel->id, collect());
                $classes = $mapelAssignments->pluck('kelas')->filter()->unique('id')->sortBy('name');
            @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $mapel->name }}</h6>
                        <span class="badge badge-secondary">{{ $mapel->code }}</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            @forelse ($classes as $kelas)
                                <span class="badge badge-light border mb-1">{{ $kelas->name }}</span>
                            @empty
                                <span class="small text-muted">Belum ada penugasan kelas.</span>
                            @endforelse
                        </div>

                        @if ($weight)
                            <div class="small text-gray-700 mb-3">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Bobot: {{ $weight->assignment_weight }}% tugas,
                                {{ $weight->uts_weight }}% UTS,
                                {{ $weight->uas_weight }}% UAS
                            </div>
                        @else
                            <div class="small text-warning mb-3">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Bobot belum dikonfigurasi (default 30 / 30 / 40).
                            </div>
                        @endif

                        <div class="mt-auto d-flex">
                            <a href="{{ route('nilai.weights', ['mapel' => $mapel->id, 'academic_year' => $academicYear->id]) }}"
                                class="btn btn-sm btn-outline-info mr-1">
                                <i class="fas fa-balance-scale fa-sm mr-1"></i> Atur Bobot
                            </a>
                            <a href="{{ route('nilai.input', ['mapel' => $mapel->id, 'academic_year' => $academicYear->id]) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-pen fa-sm mr-1"></i> Input Nilai
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-book fa-2x mb-2 text-gray-300"></i>
                        <p class="mb-0">Belum ada mata pelajaran yang dapat dinilai.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
