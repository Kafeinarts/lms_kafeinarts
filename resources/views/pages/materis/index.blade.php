@extends('layouts.app')

@section('title', 'Materi Pembelajaran - StaffWork LMS')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Materi Pembelajaran</h1>
            <p class="text-muted small mb-0">Blog materi ajar — dapat diunggah oleh Super Admin & Guru</p>
        </div>
        @if (Auth::user()->isTeacher())
            <div>
                <a href="{{ route('materis.bin') }}" class="btn btn-sm btn-outline-secondary shadow-sm mr-1">
                    <i class="fas fa-trash-restore fa-sm mr-1"></i> History Bin
                </a>
                <a href="{{ route('materis.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Upload Materi Baru
                </a>
            </div>
        @endif
    </div>

    <!-- Filter, mapel, dan urutan -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form action="{{ route('materis.index') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-lg-5 col-md-6 mb-2 mb-md-0">
                    <label for="q" class="small font-weight-bold text-gray-700 mb-1">Cari Materi</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" id="q" class="form-control bg-light"
                            placeholder="Cari judul atau isi materi..." value="{{ request('q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-lg-3 col-md-6 mb-2 mb-md-0">
                    <label for="mapel" class="small font-weight-bold text-gray-700 mb-1">Mata Pelajaran</label>
                    <select name="mapel" id="mapel" class="form-control form-control-sm bg-light">
                        <option value="">Semua Mapel (Show All)</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ (string) request('mapel') === (string) $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-2 col-md-6 mb-2 mb-md-0">
                    <label for="sort" class="small font-weight-bold text-gray-700 mb-1">Urutkan</label>
                    <select name="sort" id="sort" class="form-control form-control-sm bg-light">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="teacher" {{ request('sort') === 'teacher' ? 'selected' : '' }}>Nama Pengajar</option>
                        <option value="mapel" {{ request('sort') === 'mapel' ? 'selected' : '' }}>Nama Mapel</option>
                        <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Judul Materi</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="form-group col-lg-2 col-md-6 mb-0">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-filter fa-sm mr-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($mode === 'mapels')
        @php
            $totalMaterials = $mapelGroups->sum('total') + $unassignedTotal;
        @endphp

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="small text-muted">
                <i class="fas fa-layer-group mr-1"></i>
                {{ $mapelGroups->count() }} mata pelajaran &bull; {{ $totalMaterials }} materi
            </div>
            <div class="small text-muted d-none d-md-block">Pilih mata pelajaran untuk melihat semua materinya</div>
        </div>

        <div class="row">
            @foreach ($mapelGroups as $group)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100 mapel-card">
                        <div class="card-header py-3 d-flex align-items-center">
                            <span class="mapel-icon">
                                <i class="fas fa-book"></i>
                            </span>
                            <div class="ml-2 min-width-0">
                                <div class="mapel-card-title text-truncate">{{ $group['mapel']->name }}</div>
                                <div class="mapel-card-meta">
                                    <span class="badge badge-light border">{{ $group['mapel']->code }}</span>
                                    {{ $group['total'] }} materi
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <ul class="material-list">
                                @foreach ($group['materials'] as $material)
                                    <li class="material-list-item">
                                        <a class="material-list-link" href="{{ route('materis.show', $material) }}">
                                            <div class="min-width-0">
                                                <div class="material-list-title text-truncate">{{ $material->title }}</div>
                                                <div class="material-list-meta">
                                                    <i class="fas fa-user fa-xs mr-1"></i>{{ $material->author->name ?? '-' }}
                                                    <span class="mx-1">&bull;</span>
                                                    {{ $material->created_at->format('d M Y') }}
                                                    @if (! $material->is_published)
                                                        <span class="badge badge-secondary ml-1">Draft</span>
                                                    @endif
                                                    @if ($material->type === 'gambar')
                                                        <span class="badge badge-warning ml-1">Gambar</span>
                                                    @elseif ($material->type === 'file')
                                                        <span class="badge badge-info ml-1">File</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right material-list-arrow"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer py-2 bg-white text-right">
                            <a href="{{ route('materis.index', ['mapel' => $group['mapel']->id]) }}"
                                class="small font-weight-bold text-decoration-none">
                                Lihat semua ({{ $group['total'] }}) <i class="fas fa-arrow-right fa-xs ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($unassignedTotal > 0)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow h-100 mapel-card">
                        <div class="card-header py-3 d-flex align-items-center">
                            <span class="mapel-icon is-muted">
                                <i class="fas fa-folder-open"></i>
                            </span>
                            <div class="ml-2 min-width-0">
                                <div class="mapel-card-title text-truncate">Materi Umum</div>
                                <div class="mapel-card-meta">{{ $unassignedTotal }} materi tanpa mapel</div>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <ul class="material-list">
                                @foreach ($unassignedMaterials as $material)
                                    <li class="material-list-item">
                                        <a class="material-list-link" href="{{ route('materis.show', $material) }}">
                                            <div class="min-width-0">
                                                <div class="material-list-title text-truncate">{{ $material->title }}</div>
                                                <div class="material-list-meta">
                                                    <i class="fas fa-user fa-xs mr-1"></i>{{ $material->author->name ?? '-' }}
                                                    <span class="mx-1">&bull;</span>
                                                    {{ $material->created_at->format('d M Y') }}
                                                    @if (! $material->is_published)
                                                        <span class="badge badge-secondary ml-1">Draft</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <i class="fas fa-chevron-right material-list-arrow"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer py-2 bg-white text-right">
                            <span class="small text-muted mr-2">Belum ditautkan ke mata pelajaran</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($mapelGroups->isEmpty() && $unassignedTotal === 0)
            <div class="card shadow">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-book-open fa-3x mb-3 text-gray-300"></i>
                    <p class="mb-0">Belum ada materi yang dipublikasikan.</p>
                    @if (Auth::user()->isTeacher())
                        <a href="{{ route('materis.create') }}" class="btn btn-primary btn-sm mt-2 font-weight-bold">
                            <i class="fas fa-plus mr-1"></i> Upload Materi Pertama
                        </a>
                    @endif
                </div>
            </div>
        @endif
    @else
        <!-- Daftar materi untuk satu mapel / hasil pencarian -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h6 class="font-weight-bold text-gray-900 mb-1">
                    @if ($selectedMapel)
                        <i class="fas fa-book text-primary mr-1"></i> {{ $selectedMapel->name }}
                    @elseif (request('q'))
                        <i class="fas fa-search text-primary mr-1"></i> Hasil pencarian &ldquo;{{ request('q') }}&rdquo;
                    @else
                        <i class="fas fa-list text-primary mr-1"></i> Daftar Materi
                    @endif
                </h6>
                <div class="small text-muted">{{ $materials->total() }} materi ditemukan</div>
            </div>
            <a href="{{ route('materis.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-layer-group fa-sm mr-1"></i> Semua Mata Pelajaran
            </a>
        </div>

        <div class="row">
            @forelse ($materials as $material)
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up"
                    data-aos-delay="{{ min(($loop->index % 3) * 80, 160) }}">
                    <div class="card shadow h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="font-weight-bold text-gray-800 mb-0">
                                    <a href="{{ route('materis.show', $material) }}" class="text-decoration-none">
                                        {{ $material->title }}
                                    </a>
                                </h5>
                                <div>
                                    @if (! $material->is_published)
                                        <span class="badge badge-secondary">Draft</span>
                                    @endif
                                    @if ($material->type === 'gambar')
                                        <span class="badge badge-warning">Gambar</span>
                                    @endif
                                    @if ($material->type === 'file')
                                        <span class="badge badge-info">File</span>
                                    @endif
                                </div>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-user mr-1"></i>{{ $material->author->name ?? '-' }}
                                <span class="mx-1">&bull;</span>
                                {{ $material->created_at->format('d M Y') }}
                                @if ($material->mapel)
                                    <span class="badge badge-primary ml-1">
                                        <i class="fas fa-book mr-1"></i>{{ $material->mapel->name }}
                                    </span>
                                @endif
                                @if ($material->kelas)
                                    <span class="badge badge-warning ml-1">
                                        <i class="fas fa-door-open mr-1"></i>{{ $material->kelas->name }}
                                    </span>
                                @else
                                    <span class="badge badge-light border ml-1">Semua Kelas</span>
                                @endif
                            </p>
                            <p class="text-muted small flex-grow-1">{{ $material->excerpt }}</p>
                            @if ($material->file_name)
                                <span class="badge badge-info mb-2 align-self-start">
                                    <i class="fas fa-paperclip mr-1"></i>{{ $material->file_name }}
                                </span>
                            @endif
                            <div class="d-flex mt-auto">
                                <a href="{{ route('materis.show', $material) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    Baca Selengkapnya <i class="fas fa-arrow-right fa-sm ml-1"></i>
                                </a>
                                @if (Auth::user()->isTeacher())
                                    <form action="{{ route('materis.destroy', $material) }}" method="POST" class="ml-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Hapus materi (bisa dipulihkan dari History Bin)"
                                            data-confirm='Pindahkan materi ini ke History Bin? Materi masih bisa dipulihkan di menu History Bin.'>
                                            <i class="fas fa-trash-alt fa-sm"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body text-center text-muted py-5">
                            <i class="fas fa-book-open fa-3x mb-3 text-gray-300"></i>
                            <p class="mb-0">Tidak ada materi yang cocok dengan filter Anda.</p>
                            <a href="{{ route('materis.index') }}" class="btn btn-primary btn-sm mt-2 font-weight-bold">
                                <i class="fas fa-layer-group mr-1"></i> Lihat Semua Mata Pelajaran
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-end">
            {{ $materials->links() }}
        </div>
    @endif
@endsection
