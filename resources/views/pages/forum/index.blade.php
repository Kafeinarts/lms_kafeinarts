@extends('layouts.app')

@section('title', 'Forum Diskusi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Forum Diskusi</h1>
            <p class="text-muted small mb-0">Ruang diskusi per mata pelajaran antara guru dan siswa</p>
        </div>
        <a href="{{ route('forum.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Buat Topik
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('forum.index') }}" method="GET" class="form-inline">
                <label for="mapel" class="small font-weight-bold text-gray-700 mr-2">Mata Pelajaran</label>
                <select name="mapel" id="mapel" class="form-control form-control-sm mr-2">
                    <option value="">Semua Mapel</option>
                    @foreach ($mapels as $mapel)
                        <option value="{{ $mapel->id }}" {{ (string) request('mapel') === (string) $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter fa-sm mr-1"></i> Filter
                </button>
            </form>
        </div>
        <div class="card-body">
            @forelse ($topics as $topic)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div class="mr-3">
                            <div class="mb-1">
                                <span class="badge badge-info px-2 py-1">{{ $topic->mapel?->name ?? '-' }}</span>
                                <span class="small text-muted ml-2">
                                    <i class="fas fa-clock mr-1"></i>{{ $topic->created_at?->format('d M Y, H:i') }}
                                </span>
                            </div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">
                                <a href="{{ route('forum.show', $topic) }}" class="text-gray-900">{{ $topic->title }}</a>
                            </h6>
                            <div class="small text-muted">
                                <i class="fas fa-user mr-1"></i>{{ $topic->author?->name ?? '-' }}
                                &middot;
                                <i class="fas fa-comments mr-1"></i>{{ $topic->replies_count }} balasan
                            </div>
                        </div>
                        <a href="{{ route('forum.show', $topic) }}" class="btn btn-sm btn-outline-primary mt-2 mt-sm-0">
                            Buka Diskusi <i class="fas fa-arrow-right fa-sm ml-1"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-comments fa-2x mb-2 text-gray-300"></i>
                    <p class="mb-0">Belum ada topik diskusi.</p>
                </div>
            @endforelse

            <div class="d-flex justify-content-end">
                {{ $topics->links() }}
            </div>
        </div>
    </div>
@endsection
