@extends('layouts.app')

@section('title', 'Rapor Siswa - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Rapor Siswa</h1>
            <p class="text-muted small mb-0">{{ $academicYear->label }}</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('nilai.rapor.pdf', ['student' => $student->id, 'academic_year' => $academicYear->id]) }}"
                class="btn btn-sm btn-danger shadow-sm font-weight-bold mr-1">
                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Cetak PDF
            </a>
            @unless (Auth::user()->isStudent())
                <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
                </a>
            @endunless
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
                <div class="mb-3">
                    <div class="small text-muted text-uppercase font-weight-bold">Siswa</div>
                    <div class="h5 mb-1 font-weight-bold text-gray-900">{{ $student->name }}</div>
                    <div class="small text-gray-700">
                        NIS: {{ $student->nis ?: '-' }} &middot; Kelas: {{ $student->kelas?->name ?? '-' }}
                    </div>
                </div>

                <form action="{{ route('nilai.rapor', ['student' => $student->id]) }}" method="GET" class="form-inline mb-3">
                    <label for="academic_year" class="small font-weight-bold text-gray-700 mr-2">Tahun Ajaran</label>
                    <select name="academic_year" id="academic_year" class="form-control form-control-sm mr-2">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $year->id === $academicYear->id ? 'selected' : '' }}>
                                {{ $year->label }}{{ $year->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th style="width: 120px;">Kode</th>
                            <th>Mata Pelajaran</th>
                            <th style="width: 140px;" class="text-center">Nilai Akhir</th>
                            <th style="width: 100px;" class="text-center">Huruf</th>
                            <th>Catatan Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($finalGrades as $index => $grade)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td class="align-middle"><span class="badge badge-secondary">{{ $grade->mapel?->code ?? '-' }}</span></td>
                                <td class="align-middle font-weight-bold text-gray-900">{{ $grade->mapel?->name ?? '-' }}</td>
                                <td class="text-center align-middle font-weight-bold">{{ number_format((float) $grade->final_score, 2) }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $grade->grade_badge_class }} px-2 py-1">{{ $grade->grade_letter }}</span>
                                </td>
                                <td class="align-middle small text-muted">{{ $grade->teacher_notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada nilai akhir untuk tahun ajaran ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($finalGrades->isNotEmpty())
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="3" class="text-right">Rata-rata Nilai</th>
                                <th class="text-center">{{ number_format($average, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
