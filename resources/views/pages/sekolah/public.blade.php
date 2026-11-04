@extends('layouts.public')

@section('title', $school->name.' - Profil Sekolah')
@section('meta_description', $school->description ? \Illuminate\Support\Str::limit(strip_tags($school->description), 150) : 'Profil resmi '.$school->name)

@section('content')
    <section class="public-hero">
        <div class="container">
            <div class="public-hero-inner">
                <div class="public-hero-logo">
                    @if ($school->logo_url)
                        <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}">
                    @else
                        <i class="fas fa-school"></i>
                    @endif
                </div>
                <div class="min-width-0">
                    <div class="public-eyebrow">Profil Sekolah</div>
                    <h1>{{ $school->name }}</h1>
                    <div class="public-hero-badges">
                        @if ($school->level)
                            <span class="badge">Jenjang {{ $school->level }}</span>
                        @endif
                        @if ($school->npsn)
                            <span class="badge">NPSN {{ $school->npsn }}</span>
                        @endif
                        @if ($school->headmaster_name)
                            <span class="badge"><i class="fas fa-user-tie mr-1"></i>{{ $school->headmaster_name }}</span>
                        @endif
                    </div>
                    @if ($school->full_address)
                        <div class="public-hero-address">
                            <i class="fas fa-map-marker-alt mt-1"></i>
                            <span>{{ $school->full_address }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="container public-content">
        <!-- Statistik ringkas -->
        <div class="row mb-2">
            <div class="col-6 col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="public-stat">
                        <div class="public-stat-value">{{ $stats['guru'] }}</div>
                        <div class="public-stat-label">Guru</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="public-stat">
                        <div class="public-stat-value">{{ $stats['murid'] }}</div>
                        <div class="public-stat-label">Murid</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="public-stat">
                        <div class="public-stat-value">{{ $stats['kelas'] }}</div>
                        <div class="public-stat-label">Kelas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card shadow h-100">
                    <div class="public-stat">
                        <div class="public-stat-value">{{ $stats['mapel'] }}</div>
                        <div class="public-stat-label">Mata Pelajaran</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h6 class="public-section-title">
                            <i class="fas fa-info-circle text-primary"></i> Tentang Sekolah
                        </h6>
                        @if ($school->description)
                            <div class="public-description">{{ $school->description }}</div>
                        @else
                            <p class="text-muted small mb-0">
                                Sekolah ini belum menambahkan keterangan atau visi misi.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h6 class="public-section-title">
                            <i class="fas fa-address-book text-primary"></i> Kontak &amp; Alamat
                        </h6>
                        <ul class="public-contact-list">
                            <li>
                                <span class="public-contact-icon"><i class="fas fa-user-tie"></i></span>
                                <div>
                                    <div class="public-contact-label">Kepala Sekolah</div>
                                    <div class="public-contact-value">{{ $school->headmaster_name ?: '-' }}</div>
                                </div>
                            </li>
                            <li>
                                <span class="public-contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <div>
                                    <div class="public-contact-label">Alamat</div>
                                    <div class="public-contact-value">{{ $school->full_address ?: '-' }}</div>
                                </div>
                            </li>
                            <li>
                                <span class="public-contact-icon"><i class="fas fa-phone"></i></span>
                                <div>
                                    <div class="public-contact-label">Telepon</div>
                                    <div class="public-contact-value">{{ $school->phone ?: '-' }}</div>
                                </div>
                            </li>
                            <li>
                                <span class="public-contact-icon"><i class="fas fa-envelope"></i></span>
                                <div>
                                    <div class="public-contact-label">Email</div>
                                    @if ($school->email)
                                        <a href="mailto:{{ $school->email }}" class="public-contact-value">{{ $school->email }}</a>
                                    @else
                                        <div class="public-contact-value">-</div>
                                    @endif
                                </div>
                            </li>
                            <li>
                                <span class="public-contact-icon"><i class="fas fa-globe"></i></span>
                                <div>
                                    <div class="public-contact-label">Website</div>
                                    @if ($school->website)
                                        <a href="{{ $school->website }}" target="_blank" rel="noopener noreferrer"
                                            class="public-contact-value">{{ $school->website }}</a>
                                    @else
                                        <div class="public-contact-value">-</div>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow border-left-primary">
                    <div class="card-body">
                        <h6 class="public-section-title">
                            <i class="fas fa-share-alt text-primary"></i> Bagikan Profil
                        </h6>
                        <p class="small text-muted mb-2">Bagikan tautan profil sekolah ini kepada orang tua atau masyarakat.</p>
                        <div class="input-group input-group-sm public-share-box">
                            <input type="text" class="form-control bg-light" id="profileLink" value="{{ url()->current() }}"
                                readonly aria-label="Tautan profil sekolah">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary font-weight-bold" id="copyProfileLink">
                                    <i class="fas fa-copy mr-1"></i> Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('copyProfileLink').addEventListener('click', function () {
        var button = this;
        var input = document.getElementById('profileLink');
        var original = button.innerHTML;

        function done() {
            button.innerHTML = '<i class="fas fa-check mr-1"></i> Tersalin';
            setTimeout(function () {
                button.innerHTML = original;
            }, 1800);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(done);
            return;
        }

        input.select();
        document.execCommand('copy');
        done();
    });
</script>
@endpush
