@extends('layouts.app')

@section('title', 'Tugas & Ujian - StaffWork LMS')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Tugas & Ujian</h1>
            <p class="text-muted small mb-0">Soal pilihan ganda — dapat diunggah oleh Super Admin & Guru</p>
        </div>
        @if (Auth::user()->isTeacher())
            <a href="{{ route('exams.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Buat Tugas/Ujian Baru
            </a>
        @endif
    </div>

    <!-- Tabs: Tugas & Ujian terpisah -->
    @php
        $tabParams = request()->query();
        unset($tabParams['page']);
    @endphp
    <div class="exam-tabs mb-3" role="tablist" aria-label="Navigasi Tugas atau Ujian">
        <a class="exam-tab {{ $type === 'tugas' ? 'is-active' : '' }}" role="tab"
            aria-selected="{{ $type === 'tugas' ? 'true' : 'false' }}"
            href="{{ route('exams.index', array_merge($tabParams, ['type' => 'tugas'])) }}">
            <i class="fas fa-tasks"></i>
            <span>Tugas</span>
            <span class="exam-tab-count">{{ $typeCounts['tugas'] }}</span>
        </a>
        <a class="exam-tab {{ $type === 'ujian' ? 'is-active' : '' }}" role="tab"
            aria-selected="{{ $type === 'ujian' ? 'true' : 'false' }}"
            href="{{ route('exams.index', array_merge($tabParams, ['type' => 'ujian'])) }}">
            <i class="fas fa-file-signature"></i>
            <span>Ujian</span>
            <span class="exam-tab-count">{{ $typeCounts['ujian'] }}</span>
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('exams.index') }}" method="GET" class="form-row align-items-end">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="form-group col-lg-4 col-md-6 mb-2 mb-md-0">
                    <label for="q" class="small font-weight-bold text-gray-700 mb-1">Cari Judul</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" id="q" class="form-control bg-light"
                            placeholder="Cari judul tugas/ujian..." value="{{ request('q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label for="mapel" class="small font-weight-bold text-gray-700 mb-1">Mata Pelajaran</label>
                    <select name="mapel" id="mapel" class="form-control form-control-sm bg-light"
                        onchange="document.getElementById('guru').value=''; this.form.submit();">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ (string) request('mapel') === (string) $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label for="guru" class="small font-weight-bold text-gray-700 mb-1">Guru</label>
                    <select name="guru" id="guru" class="form-control form-control-sm bg-light"
                        onchange="this.form.submit();">
                        <option value="">Semua Guru</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ (string) request('guru') === (string) $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                    @unless (request('mapel'))
                        <small class="form-text text-muted mb-0">Pilih mapel untuk menampilkan guru pengampu.</small>
                    @endunless
                </div>
                <div class="form-group col-lg-2 col-md-6 mb-0">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter fa-sm mr-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list mr-1"></i> Daftar {{ $type === 'ujian' ? 'Ujian' : 'Tugas' }}
            </h6>
            <span class="badge badge-light border">Total: {{ $exams->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Judul</th>
                            <th style="width: 130px;">Mata Pelajaran</th>
                            <th style="width: 90px;" class="text-center">Jumlah Soal</th>
                            <th style="width: 110px;" class="text-center">Durasi</th>
                            <th style="width: 120px;">Pembuat</th>
                            <th style="width: 140px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($exams as $index => $exam)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">
                                    {{ $exams->firstItem() + $index }}
                                </td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    <a href="{{ route('exams.show', $exam) }}">{{ $exam->title }}</a>
                                    @if ($exam->description)
                                        <br><small class="text-muted font-weight-normal">{{ \Str::limit($exam->description, 60) }}</small>
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    @if ($exam->mapel)
                                        <span class="badge badge-primary">
                                            <i class="fas fa-book mr-1"></i>{{ $exam->mapel->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-light border">{{ $exam->questions_count }} soal</span>
                                </td>
                                <td class="text-center align-middle small">
                                    {{ $exam->duration_minutes ? $exam->duration_minutes . ' menit' : '-' }}
                                </td>
                                <td class="align-middle small text-muted">{{ $exam->author->name ?? '-' }}</td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-secondary" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (Auth::user()->isTeacher())
                                            <a href="{{ route('exams.edit', $exam) }}" class="btn btn-outline-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline"
                                                data-confirm='Yakin hapus tugas/ujian ini?'>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada tugas atau ujian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
@endsection
