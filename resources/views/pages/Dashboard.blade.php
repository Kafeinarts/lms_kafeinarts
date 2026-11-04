@extends('layouts.app')

@section('title', 'Dashboard - StaffWork LMS')

@section('content')


    <!-- Sapaan utama -->
    <div class="card shadow-sm mb-3" data-aos="fade-up">
    <div class="card-body py-3 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">

            {{-- Greeting: Left --}}
            <div>
                <div class="text-muted small mb-1" id="heroGreeting">
                    Selamat datang
                </div>

                <h5 class="font-weight-bold mb-1">
                    Halo, {{ $user->name }}
                </h5>

                <div class="text-muted small mb-2 mb-md-0">
                    <span id="heroDate">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</span>
                    <span class="mx-1">&bull;</span>
                    <span id="heroClock">{{ now()->format('H:i:s') }}</span>
                </div>

                <div class="small mt-1">
                    <span class="badge {{ $user->role_badge_class }} px-2 py-1">
                        {{ $user->role_name }}
                    </span>
                    <span class="text-muted mx-1">&bull;</span>
                    <span class="text-muted">{{ $user->email }}</span>
                </div>
            </div>

            {{-- Actions: Right --}}
            <div class="d-flex flex-wrap justify-content-end align-items-center" style="gap: 6px;">

                @if ($user->isTeacher())
                    <a href="{{ route('materis.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Upload Materi
                    </a>

                    <a href="{{ route('exams.create') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-clipboard-list mr-1"></i> Buat Tugas/Ujian
                    </a>
                @endif

                @if ($user->isSuperAdmin())
                    <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-user-plus mr-1"></i> Registrasi Akun
                    </a>
                @endif

                <a href="{{ route('report.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-bug mr-1"></i> Laporkan Bug
                </a>

            </div>

        </div>
    </div>
</div>
    @if ($school->hasDetails())
        <!-- Informasi sekolah (kartu utama) -->
        <div class="card shadow school-banner mb-4" data-aos="fade-up">
            <div class="card-body d-flex flex-wrap align-items-center">
                <div class="school-banner-icon">
                    @if ($school->logo_url)
                        <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}">
                    @else
                        <i class="fas fa-school"></i>
                    @endif
                </div>
                <div class="school-banner-body flex-grow-1 min-width-0 mr-3">
                    <div class="school-banner-eyebrow">Informasi Sekolah</div>
                    <h4 class="school-banner-name mb-1">{{ $school->name }}</h4>
                    <div class="school-banner-meta mb-2">
                        @if ($school->level)
                            <span class="badge badge-primary">Jenjang {{ $school->level }}</span>
                        @endif
                        @if ($school->npsn)
                            <span class="badge badge-light border">NPSN {{ $school->npsn }}</span>
                        @endif
                        @if ($school->headmaster_name)
                            <span class="badge badge-light border">
                                <i class="fas fa-user-tie mr-1"></i>Kepala Sekolah: {{ $school->headmaster_name }}
                            </span>
                        @endif
                    </div>
                    <div class="school-banner-contact small">
                        @if ($school->full_address)
                            <span class="school-banner-contact-item">
                                <i class="fas fa-map-marker-alt text-primary"></i>{{ $school->full_address }}
                            </span>
                        @endif
                        @if ($school->phone)
                            <span class="school-banner-contact-item">
                                <i class="fas fa-phone text-primary"></i>{{ $school->phone }}
                            </span>
                        @endif
                        @if ($school->email)
                            <a href="mailto:{{ $school->email }}" class="school-banner-contact-item">
                                <i class="fas fa-envelope text-primary"></i>{{ $school->email }}
                            </a>
                        @endif
                        @if ($school->website)
                            <a href="{{ $school->website }}" target="_blank" rel="noopener noreferrer"
                                class="school-banner-contact-item">
                                <i class="fas fa-globe text-primary"></i>{{ $school->website }}
                            </a>
                        @endif
                    </div>
                </div>                <div class="school-banner-actions mt-2 mt-md-0">
                    <a href="{{ route('sekolah.public') }}" target="_blank" rel="noopener"
                        class="btn btn-sm btn-outline-secondary font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Profil Publik
                    </a>
                    @if ($user->isSuperAdmin())
                        <a href="{{ route('sekolah.edit') }}" class="btn btn-sm btn-outline-primary font-weight-bold ml-1">
                            <i class="fas fa-edit mr-1"></i> Edit Informasi
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
    <!-- Ringkasan singkat -->
    <div class="row mb-2">
        <div class="col-6 col-lg mb-3" data-aos="fade-up" data-aos-delay="40">
            <div class="card compact-stat border-left-primary h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="compact-stat-label">Total Akun</div>
                        <div class="compact-stat-value">{{ $totalUsers }}</div>
                    </div>
                    <i class="fas fa-users compact-stat-icon text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3" data-aos="fade-up" data-aos-delay="80">
            <div class="card compact-stat border-left-info h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="compact-stat-label">Guru</div>
                        <div class="compact-stat-value">{{ $totalGuru }}</div>
                    </div>
                    <i class="fas fa-chalkboard-teacher compact-stat-icon text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3" data-aos="fade-up" data-aos-delay="120">
            <div class="card compact-stat border-left-success h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="compact-stat-label">Murid</div>
                        <div class="compact-stat-value">{{ $totalMurid }}</div>
                    </div>
                    <i class="fas fa-user-graduate compact-stat-icon text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3" data-aos="fade-up" data-aos-delay="160">
            <div class="card compact-stat border-left-warning h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="compact-stat-label">Materi</div>
                        <div class="compact-stat-value">{{ $totalMaterials }}</div>
                    </div>
                    <i class="fas fa-book-open compact-stat-icon text-warning"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card compact-stat border-left-secondary h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="compact-stat-label">Tugas &amp; Ujian</div>
                        <div class="compact-stat-value">{{ $totalExams }}</div>
                    </div>
                    <i class="fas fa-clipboard-list compact-stat-icon text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kalender & log aktivitas -->
    <div class="row">
        <div class="col-lg-7 mb-4" data-aos="fade-up">
            <div class="card calendar-card h-100">
                <div class="calendar-header">
                    <div>
                        <h6 class="calendar-title mb-0">Kalender</h6>
                        <div class="small text-muted">Jadwal &amp; tanggal penting</div>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="calendar-nav mr-1" id="calendarPrev" title="Bulan sebelumnya">
                            <i class="fas fa-chevron-left fa-sm"></i>
                        </button>
                        <span class="calendar-title mx-2" id="calendarTitle">&nbsp;</span>
                        <button type="button" class="calendar-nav ml-1" id="calendarNext" title="Bulan berikutnya">
                            <i class="fas fa-chevron-right fa-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="calendar-grid" id="calendarGrid"></div>
                <div class="calendar-legend">
                    <span class="dot"></span> Hari ini
                    <span class="dot dot-weekend ml-3"></span> Akhir pekan
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4" data-aos="fade-up" data-aos-delay="80">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-1"></i>
                        {{ $user->isSuperAdmin() ? 'Aktivitas Terbaru' : 'Aktivitas Saya' }}
                    </h6>
                    @if ($user->isSuperAdmin())
                        <a href="{{ route('activity.index') }}" class="small font-weight-bold">Lihat semua &rarr;</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <ul class="activity-list mb-0">
                        @forelse ($recentActivities as $activity)
                            <li class="activity-item">
                                <span
                                    class="activity-dot {{ in_array($activity->action, ['login', 'logout'], true) ? 'is-muted' : '' }}"></span>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="small text-gray-800 font-weight-bold text-truncate">
                                        {{ $activity->user->name ?? 'Sistem' }}
                                        <span
                                            class="badge {{ $activity->action_badge_class }} ml-1">{{ $activity->action_label }}</span>
                                    </div>
                                    <div class="small text-muted text-truncate">{{ $activity->description }}</div>
                                </div>
                                <div class="small text-muted ml-2 text-nowrap">
                                    {{ $activity->created_at?->format('d M H:i') }}
                                </div>
                            </li>
                        @empty
                            <li class="activity-item text-center text-muted small py-4">
                                Belum ada aktivitas tercatat.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Materi terbaru -->
    <div class="card shadow mb-4" data-aos="fade-up">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-book-open mr-1"></i> Materi Terbaru
            </h6>
            <a href="{{ route('materis.index') }}" class="small font-weight-bold">Semua materi &rarr;</a>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse ($recentMaterials as $material)
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('materis.show', $material) }}"
                            class="d-flex justify-content-between align-items-start text-decoration-none p-3 rounded bg-light h-100">
                            <div class="mr-3">
                                <div class="font-weight-bold text-gray-800 mb-1">{{ $material->title }}</div>
                                <div class="small text-muted">
                                    <i class="fas fa-user mr-1"></i>{{ $material->author->name ?? '-' }}
                                    <span class="mx-1">&bull;</span>{{ $material->created_at?->format('d M Y') }}
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 mt-1"></i>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted small py-3">Belum ada materi.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            var dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            var today = new Date();
            var viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
            var grid = document.getElementById('calendarGrid');
            var title = document.getElementById('calendarTitle');

            function renderCalendar() {
                var year = viewDate.getFullYear();
                var month = viewDate.getMonth();
                title.textContent = monthNames[month] + ' ' + year;

                var startOffset = (new Date(year, month, 1).getDay() + 6) % 7;
                var daysInMonth = new Date(year, month + 1, 0).getDate();
                var prevMonthDays = new Date(year, month, 0).getDate();
                var html = dayNames.map(function(day) {
                    return '<div class="calendar-weekday">' + day + '</div>';
                }).join('');

                for (var i = 0; i < startOffset; i++) {
                    html += '<span class="calendar-day is-muted">' + (prevMonthDays - startOffset + i + 1) +
                        '</span>';
                }

                for (var day = 1; day <= daysInMonth; day++) {
                    var date = new Date(year, month, day);
                    var dow = date.getDay();
                    var classes = ['calendar-day'];

                    if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                        classes.push('is-today');
                    } else if (dow === 0 || dow === 6) {
                        classes.push('is-weekend');
                    }

                    html += '<span class="' + classes.join(' ') + '">' + day + '</span>';
                }

                var trailing = (7 - ((startOffset + daysInMonth) % 7)) % 7;
                for (var t = 1; t <= trailing; t++) {
                    html += '<span class="calendar-day is-muted">' + t + '</span>';
                }

                grid.innerHTML = html;
            }

            document.getElementById('calendarPrev').addEventListener('click', function() {
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
                renderCalendar();
            });

            document.getElementById('calendarNext').addEventListener('click', function() {
                viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
                renderCalendar();
            });

            renderCalendar();

            function updateClock() {
                var now = new Date();
                var pad = function(value) {
                    return String(value).padStart(2, '0');
                };

                document.getElementById('heroClock').textContent =
                    pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());

                document.getElementById('heroDate').textContent = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                var hour = now.getHours();
                var greeting = 'Selamat malam';

                if (hour >= 4 && hour < 11) {
                    greeting = 'Selamat pagi';
                } else if (hour >= 11 && hour < 15) {
                    greeting = 'Selamat siang';
                } else if (hour >= 15 && hour < 18) {
                    greeting = 'Selamat sore';
                }

                document.getElementById('heroGreeting').textContent = greeting;
            }

            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
@endpush
