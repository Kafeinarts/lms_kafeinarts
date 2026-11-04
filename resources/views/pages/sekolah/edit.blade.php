@extends('layouts.app')

@section('title', 'Informasi Sekolah - StaffWork')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Informasi Sekolah</h1>
            <p class="text-muted small mb-0">Kelola identitas, alamat, dan kontak resmi sekolah</p>
        </div>
        <div class="d-flex flex-wrap align-items-center">
            <a href="{{ route('sekolah.public') }}" target="_blank" rel="noopener"
                class="btn btn-sm btn-outline-primary shadow-sm font-weight-bold mr-1 mb-1 mb-sm-0">
                <i class="fas fa-external-link-alt fa-sm mr-1"></i> Lihat Profil Publik
            </a>
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-secondary shadow-sm mb-1 mb-sm-0">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Akun Saya
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-school mr-1"></i> Formulir Informasi Sekolah
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sekolah.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Logo Sekolah -->
                        <h6 class="form-section-title">
                            <i class="fas fa-image mr-1"></i> Logo Sekolah
                        </h6>

                        <div class="form-group">
                            <div class="d-flex align-items-center">
                                <div class="school-logo-preview mr-3" id="logoPreviewBox">
                                    @if ($school->logo_url)
                                        <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}" id="logoPreview">
                                    @else
                                        <i class="fas fa-school" id="logoPlaceholder"></i>
                                        <img src="" alt="Pratinjau logo" id="logoPreview" class="d-none">
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="logo"
                                            class="custom-file-input @error('logo') is-invalid @enderror"
                                            accept="image/*">
                                        <label class="custom-file-label" for="logo">Pilih file logo sekolah...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Format: JPG, PNG, WEBP, atau SVG. Ukuran maksimal 2MB. Logo tampil di Dashboard dan Profil Publik.
                                    </small>
                                    @error('logo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Identitas Sekolah -->
                        <h6 class="form-section-title">
                            <i class="fas fa-id-badge mr-1"></i> Identitas Sekolah
                        </h6>

                        <div class="form-group">
                            <label for="name" class="font-weight-bold text-gray-700">Nama Sekolah <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $school->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="npsn" class="font-weight-bold text-gray-700">NPSN</label>
                                <input type="text" name="npsn" id="npsn"
                                    class="form-control @error('npsn') is-invalid @enderror"
                                    value="{{ old('npsn', $school->npsn) }}" placeholder="Nomor Pokok Sekolah Nasional">
                                @error('npsn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="level" class="font-weight-bold text-gray-700">Jenjang</label>
                                <select name="level" id="level" class="form-control @error('level') is-invalid @enderror">
                                    <option value="">Pilih jenjang...</option>
                                    @foreach (['SD', 'MI', 'SMP', 'MTs', 'SMA', 'MA', 'SMK', 'MAK'] as $level)
                                        <option value="{{ $level }}" {{ old('level', $school->level) === $level ? 'selected' : '' }}>
                                            {{ $level }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="headmaster_name" class="font-weight-bold text-gray-700">Nama Kepala Sekolah</label>
                            <input type="text" name="headmaster_name" id="headmaster_name"
                                class="form-control @error('headmaster_name') is-invalid @enderror"
                                value="{{ old('headmaster_name', $school->headmaster_name) }}">
                            @error('headmaster_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <!-- Alamat Sekolah -->
                        <h6 class="form-section-title">
                            <i class="fas fa-map-marker-alt mr-1"></i> Alamat Sekolah
                        </h6>

                        <div class="form-group">
                            <label for="address" class="font-weight-bold text-gray-700">Alamat Lengkap</label>
                            <textarea name="address" id="address" rows="3"
                                class="form-control @error('address') is-invalid @enderror"
                                placeholder="Jalan, nomor, RT/RW, kelurahan, kecamatan">{{ old('address', $school->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label for="city" class="font-weight-bold text-gray-700">Kota / Kabupaten</label>
                                <input type="text" name="city" id="city"
                                    class="form-control @error('city') is-invalid @enderror"
                                    value="{{ old('city', $school->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="province" class="font-weight-bold text-gray-700">Provinsi</label>
                                <input type="text" name="province" id="province"
                                    class="form-control @error('province') is-invalid @enderror"
                                    value="{{ old('province', $school->province) }}">
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="postal_code" class="font-weight-bold text-gray-700">Kode Pos</label>
                                <input type="text" name="postal_code" id="postal_code"
                                    class="form-control @error('postal_code') is-invalid @enderror"
                                    value="{{ old('postal_code', $school->postal_code) }}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- Kontak & Keterangan -->
                        <h6 class="form-section-title">
                            <i class="fas fa-address-book mr-1"></i> Kontak &amp; Keterangan
                        </h6>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phone" class="font-weight-bold text-gray-700">Nomor Telepon</label>
                                <input type="text" name="phone" id="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $school->phone) }}" placeholder="021-1234567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email" class="font-weight-bold text-gray-700">Email Sekolah</label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $school->email) }}" placeholder="info@sekolah.sch.id">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="website" class="font-weight-bold text-gray-700">Website</label>
                            <input type="text" name="website" id="website"
                                class="form-control @error('website') is-invalid @enderror"
                                value="{{ old('website', $school->website) }}" placeholder="https://sekolah.sch.id">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="font-weight-bold text-gray-700">Keterangan / Visi Misi</label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Deskripsi singkat sekolah, visi misi, atau catatan lain">{{ old('description', $school->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-save mr-1"></i> Simpan Informasi Sekolah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Ringkasan Sekolah -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i> Ringkasan Sekolah
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="school-logo-preview is-sm mr-3">
                            @if ($school->logo_url)
                                <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}">
                            @else
                                <i class="fas fa-school"></i>
                            @endif
                        </div>
                        <div class="min-width-0">
                            <div class="school-summary-name text-truncate">{{ $school->name }}</div>
                            <div class="small text-muted">
                                {{ $school->level ? 'Jenjang '.$school->level : 'Jenjang belum diisi' }}
                                @if ($school->npsn)
                                    &bull; NPSN {{ $school->npsn }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <ul class="list-unstyled small mb-0 school-summary-list">
                        <li>
                            <i class="fas fa-user-tie fa-fw text-primary mr-1"></i>
                            <span class="text-muted">Kepala Sekolah:</span>
                            {{ $school->headmaster_name ?: '-' }}
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw text-primary mr-1"></i>
                            <span class="text-muted">Alamat:</span>
                            {{ $school->full_address ?: '-' }}
                        </li>
                        <li>
                            <i class="fas fa-phone fa-fw text-primary mr-1"></i>
                            <span class="text-muted">Telepon:</span>
                            {{ $school->phone ?: '-' }}
                        </li>
                        <li>
                            <i class="fas fa-envelope fa-fw text-primary mr-1"></i>
                            <span class="text-muted">Email:</span>
                            {{ $school->email ?: '-' }}
                        </li>
                        <li>
                            <i class="fas fa-globe fa-fw text-primary mr-1"></i>
                            <span class="text-muted">Website:</span>
                            {{ $school->website ?: '-' }}
                        </li>
                    </ul>

                    @if ($school->logo_url)
                        <form action="{{ route('sekolah.logo.destroy') }}" method="POST" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger btn-block"
                                data-confirm='Hapus logo sekolah? Logo akan hilang dari Dashboard dan Profil Publik.'>
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Logo Sekolah
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <h6 class="font-weight-bold text-gray-900 mb-2">
                        <i class="fas fa-lightbulb text-warning mr-1"></i> Tips
                    </h6>
                    <p class="small text-muted mb-0">
                        Lengkapi alamat dan kontak agar mudah ditemukan orang tua murid. Informasi ini hanya dapat
                        diubah oleh Super Admin melalui menu <strong>Akun Saya &rarr; Informasi Sekolah</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Pratinjau logo sebelum disimpan.
    $('#logo').on('change', function (event) {
        var file = event.target.files[0];

        if (!file) {
            return;
        }

        $(this).next('.custom-file-label').text(file.name);

        var reader = new FileReader();
        reader.onload = function (e) {
            $('#logoPreview').attr('src', e.target.result).removeClass('d-none');
            $('#logoPlaceholder').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
