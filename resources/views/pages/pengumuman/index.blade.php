@extends('layouts.app')

@section('title', 'Pengumuman - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Pengumuman</h1>
            <p class="text-muted small mb-0">Informasi dan berita terbaru dari sekolah</p>
        </div>
        @if (Auth::user()->isTeacher())
            <a href="{{ route('pengumuman.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Buat Pengumuman
            </a>
        @endif
    </div>

    @forelse ($announcements as $announcement)
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-start justify-content-between">
                    <div class="mr-3">
                        <div class="mb-1">
                            <span class="badge {{ $announcement->audience_badge_class }} px-2 py-1">{{ $announcement->audience_label }}</span>
                            <span class="small text-muted ml-2">
                                <i class="fas fa-clock mr-1"></i>{{ $announcement->published_at?->format('d M Y, H:i') ?? $announcement->created_at?->format('d M Y, H:i') }}
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-gray-900 mb-1">{{ $announcement->title }}</h5>
                        <div class="small text-muted mb-2">
                            <i class="fas fa-user mr-1"></i>{{ $announcement->author?->name ?? '-' }}
                        </div>
                        <p class="mb-0 text-gray-700">{{ \Illuminate\Support\Str::limit($announcement->body, 220) }}</p>
                    </div>

                    <div class="d-flex flex-shrink-0 mt-2 mt-md-0">
                        <a href="{{ route('pengumuman.show', $announcement) }}" class="btn btn-sm btn-outline-primary mr-1">
                            <i class="fas fa-book-open fa-sm mr-1"></i> Baca
                        </a>
                        @if (Auth::user()->isSuperAdmin() || Auth::id() === $announcement->user_id)
                            <a href="{{ route('pengumuman.edit', $announcement) }}" class="btn btn-sm btn-outline-info" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow mb-4">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-bullhorn fa-2x mb-2 text-gray-300"></i>
                <p class="mb-0">Belum ada pengumuman untuk Anda.</p>
            </div>
        </div>
    @endforelse

    <div class="d-flex justify-content-end">
        {{ $announcements->links() }}
    </div>
@endsection
