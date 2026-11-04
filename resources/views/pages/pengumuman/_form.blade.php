@php $editing = isset($announcement); @endphp

<form action="{{ $editing ? route('pengumuman.update', $announcement) : route('pengumuman.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="form-group">
        <label for="title" class="font-weight-bold text-gray-700">Judul Pengumuman <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title"
            value="{{ old('title', $announcement->title ?? '') }}"
            class="form-control @error('title') is-invalid @enderror"
            placeholder="Contoh: Jadwal Ujian Tengah Semester" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="audience" class="font-weight-bold text-gray-700">Target Pembaca <span class="text-danger">*</span></label>
        <select name="audience" id="audience" class="form-control @error('audience') is-invalid @enderror" required>
            @foreach ($audiences as $value => $label)
                <option value="{{ $value }}" {{ old('audience', $announcement->audience ?? 'all') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('audience')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="body" class="font-weight-bold text-gray-700">Isi Pengumuman <span class="text-danger">*</span></label>
        <textarea name="body" id="body" rows="8"
            class="form-control @error('body') is-invalid @enderror"
            placeholder="Tulis informasi yang ingin disampaikan..." required>{{ old('body', $announcement->body ?? '') }}</textarea>
        @error('body')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <p class="small text-muted">
        <i class="fas fa-bell mr-1"></i> Setiap pengumuman yang dipublikasikan akan mengirim notifikasi ke pembaca yang dituju.
    </p>

    <div class="d-flex justify-content-end">
        <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary mr-2">Batal</a>
        <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
            <i class="fas fa-paper-plane mr-1"></i> {{ $editing ? 'Simpan Perubahan' : 'Publikasikan' }}
        </button>
    </div>
</form>
