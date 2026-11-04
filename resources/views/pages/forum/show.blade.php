@extends('layouts.app')

@section('title', 'Diskusi - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Diskusi</h1>
            <p class="text-muted small mb-0">
                <span class="badge badge-info">{{ $topic->mapel?->name ?? '-' }}</span>
                &middot; {{ $topic->replies->count() }} balasan
            </p>
        </div>
        <a href="{{ route('forum.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
            <div class="small text-muted">
                <i class="fas fa-user mr-1"></i>{{ $topic->author?->name ?? '-' }}
                <span class="mx-1">&middot;</span>
                {{ $topic->created_at?->format('d M Y, H:i') }}
            </div>
            @if (Auth::user()->isSuperAdmin() || Auth::id() === $topic->user_id)
                <form action="{{ route('forum.destroy', $topic) }}" method="POST" class="mt-2 mt-sm-0"
                    data-confirm='Hapus topik "{{ $topic->title }}"? Semua balasan juga akan dihapus.'>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash-alt fa-sm mr-1"></i> Hapus Topik
                    </button>
                </form>
            @endif
        </div>
        <div class="card-body">
            <h4 class="font-weight-bold text-gray-900 mb-3">{{ $topic->title }}</h4>
            <div class="text-gray-800" style="white-space: pre-line; line-height: 1.8;">{{ $topic->body }}</div>
        </div>
    </div>

    <h5 class="font-weight-bold text-gray-800 mb-3">
        <i class="fas fa-comments mr-1 text-primary"></i> Balasan ({{ $topic->replies->count() }})
    </h5>

    @forelse ($topic->replies as $reply)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-start justify-content-between">
                    <div class="small text-muted mb-2">
                        <i class="fas fa-user-circle mr-1"></i>
                        <span class="font-weight-bold text-gray-800">{{ $reply->author?->name ?? '-' }}</span>
                        <span class="mx-1">&middot;</span>
                        {{ $reply->created_at?->format('d M Y, H:i') }}
                    </div>
                    @if (Auth::user()->isSuperAdmin() || Auth::id() === $reply->user_id)
                        <form action="{{ route('forum.reply.destroy', $reply) }}" method="POST"
                            data-confirm='Hapus balasan ini?'>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus balasan">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    @endif
                </div>
                <div class="text-gray-800" style="white-space: pre-line;">{{ $reply->body }}</div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border text-muted">
            Belum ada balasan. Jadilah yang pertama membalas topik ini.
        </div>
    @endforelse

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-reply mr-1"></i> Tulis Balasan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('forum.reply', $topic) }}" method="POST">
                @csrf
                <div class="form-group">
                    <textarea name="body" id="body" rows="4"
                        class="form-control @error('body') is-invalid @enderror"
                        placeholder="Tulis balasan Anda..." required>{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Balasan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
