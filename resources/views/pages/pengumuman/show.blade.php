@extends('layouts.app')

@section('title', 'Detail Pengumuman - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Detail Pengumuman</h1>
            <p class="text-muted small mb-0">Dibaca oleh: {{ $announcement->audience_label }}</p>
        </div>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <span class="badge {{ $announcement->audience_badge_class }} px-2 py-1">{{ $announcement->audience_label }}</span>
                        <span class="small text-muted ml-2">
                            {{ $announcement->published_at?->format('d M Y, H:i') ?? $announcement->created_at?->format('d M Y, H:i') }}
                        </span>
                    </div>
                    @if (Auth::user()->isSuperAdmin() || Auth::id() === $announcement->user_id)
                        <div class="d-flex mt-2 mt-sm-0">
                            <a href="{{ route('pengumuman.edit', $announcement) }}" class="btn btn-sm btn-outline-info mr-1">
                                <i class="fas fa-edit fa-sm mr-1"></i> Edit
                            </a>
                            <form action="{{ route('pengumuman.destroy', $announcement) }}" method="POST" class="d-inline"
                                data-confirm='Hapus pengumuman "{{ $announcement->title }}"?'>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt fa-sm mr-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <h3 class="font-weight-bold text-gray-900 mb-3">{{ $announcement->title }}</h3>
                    <div class="small text-muted mb-4">
                        <i class="fas fa-user mr-1"></i>{{ $announcement->author?->name ?? '-' }}
                    </div>
                    <div class="text-gray-800" style="white-space: pre-line; line-height: 1.8;">{{ $announcement->body }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
