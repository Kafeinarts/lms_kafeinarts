@extends('layouts.app')

@section('title', 'Input Nilai - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Input Nilai</h1>
            <p class="text-muted small mb-0">Nilai tugas diambil otomatis; masukkan nilai UTS dan UAS untuk menghitung nilai akhir</p>
        </div>
        <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('nilai.input') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-lg-3 col-md-6 mb-md-0">
                    <label for="academic_year" class="small font-weight-bold text-gray-700">Tahun Ajaran</label>
                    <select name="academic_year" id="academic_year" class="form-control form-control-sm">
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $year->id === $academicYear->id ? 'selected' : '' }}>
                                {{ $year->label }}{{ $year->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-md-0">
                    <label for="mapel" class="small font-weight-bold text-gray-700">Mata Pelajaran</label>
                    <select name="mapel" id="mapel" class="form-control form-control-sm">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ (string) request('mapel') === (string) $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-md-0">
                    <label for="kelas" class="small font-weight-bold text-gray-700">Kelas</label>
                    <select name="kelas" id="kelas" class="form-control form-control-sm">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) request('kelas') === (string) $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-0">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search fa-sm mr-1"></i> Tampilkan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($selectedMapel === null || $selectedKelas === null)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Pilih mata pelajaran dan kelas terlebih dahulu untuk menampilkan daftar siswa.
        </div>
    @elseif ($students->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada siswa terdaftar di kelas {{ $selectedKelas->name }}.
        </div>
    @else
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pen mr-1"></i> {{ $selectedMapel->name }} &middot; {{ $selectedKelas->name }}
                </h6>
                <span class="badge badge-light border">
                    Bobot: {{ $weight->assignment_weight }} / {{ $weight->uts_weight }} / {{ $weight->uas_weight }}
                </span>
            </div>
            <div class="card-body">
                <form action="{{ route('nilai.input.store') }}" method="POST" id="gradeForm"
                    data-assignment-weight="{{ $weight->assignment_weight }}"
                    data-uts-weight="{{ $weight->uts_weight }}"
                    data-uas-weight="{{ $weight->uas_weight }}">
                    @csrf
                    <input type="hidden" name="mapel_id" value="{{ $selectedMapel->id }}">
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelas->id }}">
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 55px;" class="text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th style="width: 115px;" class="text-center">Nilai Tugas</th>
                                    <th style="width: 110px;">UTS</th>
                                    <th style="width: 110px;">UAS</th>
                                    <th style="width: 110px;" class="text-center">Nilai Akhir</th>
                                    <th style="width: 80px;" class="text-center">Huruf</th>
                                    <th style="width: 210px;">Catatan Guru</th>
                                    <th style="width: 90px;" class="text-center">Rapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    @php $row = $rows[$student->id] ?? []; @endphp
                                    <tr data-student="{{ $student->id }}" data-tugas="{{ $row['tugas'] ?? 0 }}">
                                        <td class="text-center align-middle font-weight-bold text-muted">{{ $index + 1 }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-gray-900">{{ $student->name }}</div>
                                            <div class="small text-muted">NIS: {{ $student->nis ?: '-' }}</div>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if (isset($row['tugas']) && $row['tugas'] !== null)
                                                <span class="badge badge-light border">{{ number_format($row['tugas'], 2) }}</span>
                                            @else
                                                <span class="text-muted small">Belum ada</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="scores[{{ $student->id }}][uts]"
                                                value="{{ old("scores.{$student->id}.uts", $row['uts'] ?? '') }}"
                                                class="form-control form-control-sm score-uts" required>
                                        </td>
                                        <td class="align-middle">
                                            <input type="number" step="0.01" min="0" max="100"
                                                name="scores[{{ $student->id }}][uas]"
                                                value="{{ old("scores.{$student->id}.uas", $row['uas'] ?? '') }}"
                                                class="form-control form-control-sm score-uas" required>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="font-weight-bold text-gray-900 final-score">
                                                {{ isset($row['final']) && $row['final'] !== null ? number_format($row['final'], 2) : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light border final-letter">{{ $row['letter'] ?? '—' }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <input type="text" name="scores[{{ $student->id }}][notes]"
                                                value="{{ old("scores.{$student->id}.notes", $row['notes'] ?? '') }}"
                                                class="form-control form-control-sm" placeholder="Opsional">
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('nilai.rapor', ['student' => $student->id, 'academic_year' => $academicYear->id]) }}"
                                                class="btn btn-sm btn-outline-info" title="Lihat rapor">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan &amp; Hitung Nilai Akhir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        // Hitung ulang pratinjau nilai akhir saat nilai UTS/UAS diubah.
        (function () {
            var form = document.getElementById('gradeForm');

            if (!form) {
                return;
            }

            var weights = {
                assignment: Number(form.dataset.assignmentWeight || 0),
                uts: Number(form.dataset.utsWeight || 0),
                uas: Number(form.dataset.uasWeight || 0)
            };

            function gradeLetter(score) {
                if (score >= 90) return 'A';
                if (score >= 80) return 'B';
                if (score >= 70) return 'C';
                if (score >= 60) return 'D';
                return 'F';
            }

            function recalculate() {
                form.querySelectorAll('tr[data-student]').forEach(function (row) {
                    var uts = row.querySelector('.score-uts');
                    var uas = row.querySelector('.score-uas');
                    var finalCell = row.querySelector('.final-score');
                    var letterCell = row.querySelector('.final-letter');

                    if (!uts || !uas || uts.value === '' || uas.value === '') {
                        finalCell.textContent = '—';
                        letterCell.textContent = '—';
                        return;
                    }

                    var assignment = Number(row.dataset.tugas || 0);
                    var score = (assignment * weights.assignment / 100)
                        + (Number(uts.value) * weights.uts / 100)
                        + (Number(uas.value) * weights.uas / 100);

                    finalCell.textContent = score.toFixed(2);
                    letterCell.textContent = gradeLetter(score);
                });
            }

            form.querySelectorAll('.score-uts, .score-uas').forEach(function (input) {
                input.addEventListener('input', recalculate);
            });
        })();
    </script>
@endpush
