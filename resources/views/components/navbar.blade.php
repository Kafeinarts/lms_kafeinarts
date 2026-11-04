<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    @php
        $currentUser = Auth::user();
        // once(): hasil query di-memo per request, aman bila partial dirender ulang.
        // Dijaga null agar halaman error (mis. 403) tetap ter-render untuk tamu tanpa sesi.
        $unreadCount = $currentUser ? once(fn () => $currentUser->unreadNotifications()->count()) : 0;
        $latestNotifications = $currentUser
            ? once(fn () => $currentUser->notifications()->latest('created_at')->take(5)->get())
            : collect();
    @endphp

    <!-- Brand (mobile) -->
    <a class="navbar-brand d-md-none mr-auto mb-0 font-weight-bold text-gray-800" href="{{ route('dashboard') }}">
        <i class="fas fa-graduation-cap text-primary mr-1"></i>MySchool
    </a>

    <!-- Topbar Search -->
    <form action="{{ route('materis.index') }}" method="GET"
        class="d-none d-md-inline-block form-inline mr-auto my-2 my-md-0 navbar-search">
        <div class="input-group">
            <input type="text" name="q" value="{{ request()->routeIs('materis.index') ? request('q') : '' }}"
                class="form-control bg-light border-0 small" placeholder="Cari materi pembelajaran..."
                aria-label="Search" aria-describedby="topbarSearch">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit" id="topbarSearch">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto align-items-center">

        <!-- Show more / menu cepat -->
        <li class="nav-item dropdown no-arrow mx-1 topbar-quick-actions">
            <a class="nav-link dropdown-toggle dropdown-toggle-no-caret" href="#" id="quickActionsDropdown"
                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                title="Menu lainnya">
                <i class="fas fa-ellipsis-v fa-fw"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="quickActionsDropdown">
                <h6 class="dropdown-header">Menu Cepat</h6>
                <a class="dropdown-item" href="{{ route('materis.index') }}">
                    <i class="fas fa-book-open fa-sm fa-fw mr-2 text-gray-400"></i> Materi Pembelajaran
                </a>
                <a class="dropdown-item" href="{{ route('exams.index') }}">
                    <i class="fas fa-clipboard-list fa-sm fa-fw mr-2 text-gray-400"></i> Tugas &amp; Ujian
                </a>
                <a class="dropdown-item" href="{{ route('jadwal.index') }}">
                    <i class="fas fa-calendar-alt fa-sm fa-fw mr-2 text-gray-400"></i> Jadwal Mengajar
                </a>
                <a class="dropdown-item" href="{{ route('penugasan.index') }}">
                    <i class="fas fa-chalkboard fa-sm fa-fw mr-2 text-gray-400"></i> Kelas Diajar
                </a>

                @if ($currentUser && $currentUser->isTeacher())
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('materis.create') }}">
                        <i class="fas fa-plus-circle fa-sm fa-fw mr-2 text-gray-400"></i> Upload Materi
                    </a>
                    <a class="dropdown-item" href="{{ route('exams.create') }}">
                        <i class="fas fa-plus-circle fa-sm fa-fw mr-2 text-gray-400"></i> Buat Tugas/Ujian
                    </a>
                @endif

                @if ($currentUser && $currentUser->isSuperAdmin())
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('users.index') }}">
                        <i class="fas fa-users-cog fa-sm fa-fw mr-2 text-gray-400"></i> Kelola Akun
                    </a>
                    <a class="dropdown-item" href="{{ route('users.create') }}">
                        <i class="fas fa-user-plus fa-sm fa-fw mr-2 text-gray-400"></i> Registrasi Akun
                    </a>
                    <a class="dropdown-item" href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher fa-sm fa-fw mr-2 text-gray-400"></i> Data Guru
                    </a>
                    <a class="dropdown-item" href="{{ route('siswa.index') }}">
                        <i class="fas fa-user-graduate fa-sm fa-fw mr-2 text-gray-400"></i> Data Siswa
                    </a>
                @endif

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('notifications.index') }}">
                    <i class="fas fa-bell fa-sm fa-fw mr-2 text-gray-400"></i> Notifikasi
                </a>
                <a class="dropdown-item" href="{{ route('report.index') }}">
                    <i class="fas fa-bug fa-sm fa-fw mr-2 text-warning"></i> Laporkan Bug / Request
                </a>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-circle fa-sm fa-fw mr-2 text-gray-400"></i> Profil &amp; Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Keluar
                </a>
            </div>
        </li>

        <!-- Nav Item - Notifications -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Notifikasi">
                <i class="fas fa-bell fa-fw"></i>
                @if ($unreadCount > 0)
                    <span class="badge badge-danger badge-counter">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
            <!-- Dropdown - Notifications -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Notifikasi
                </h6>
                @forelse ($latestNotifications as $notification)
                    @php $data = $notification->data; @endphp
                    <a class="dropdown-item d-flex align-items-center {{ $notification->read_at ? '' : 'bg-light' }}"
                        href="{{ route('notifications.index') }}">
                        <div class="mr-3">
                            <div class="icon-circle {{ ($data['room'] ?? '') === 'bk' ? 'bg-danger' : ((($data['room'] ?? '') === 'guru') ? 'bg-warning' : 'bg-primary') }}">
                                <i class="fas {{ ($data['room'] ?? '') !== '' ? 'fa-bell text-white' : 'fa-info text-white' }}"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">{{ $notification->created_at?->format('d M Y, H:i') }}</div>
                            <span class="{{ $notification->read_at ? 'text-gray-800' : 'font-weight-bold' }}">
                                {{ $data['title'] ?? 'Notifikasi' }}
                            </span>
                            <div class="small text-gray-600">{{ \Illuminate\Support\Str::limit($data['message'] ?? '', 50) }}</div>
                        </div>
                    </a>
                @empty
                    <span class="dropdown-item-text text-muted small">Belum ada notifikasi.</span>
                @endforelse
                <a class="dropdown-item text-center small font-weight-bold text-primary" href="{{ route('notifications.index') }}">
                    Lihat Semua Notifikasi <i class="fas fa-arrow-right fa-sm ml-1"></i>
                </a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        @if ($currentUser)
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">
                    {{ $currentUser->name }}
                </span>
                <img class="img-profile rounded-circle border"
                    src="{{ $currentUser->avatar_url }}" alt="Profile"
                    style="object-fit: cover; width: 32px; height: 32px;">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <div class="dropdown-item-text text-muted small pb-2 border-bottom">
                    <div class="font-weight-bold text-gray-800">{{ $currentUser->name }}</div>
                    <div>{{ $currentUser->email }}</div>
                    <span class="badge {{ $currentUser->role_badge_class }} mt-1">
                        {{ $currentUser->role_name }}
                    </span>
                </div>
                <a class="dropdown-item mt-2" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profil Saya
                </a>
                <a class="dropdown-item" href="{{ route('report.index') }}">
                    <i class="fas fa-bug fa-sm fa-fw mr-2 text-gray-400"></i>
                    Laporkan Bug / Request
                </a>
                @if ($currentUser && $currentUser->isSuperAdmin())
                    <a class="dropdown-item" href="{{ route('users.index') }}">
                        <i class="fas fa-users-cog fa-sm fa-fw mr-2 text-gray-400"></i>
                        Kelola Pengguna
                    </a>
                @endif
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    Keluar (Logout)
                </a>
            </div>
        </li>
        @else
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">Masuk</span>
                    <i class="fas fa-sign-in-alt fa-fw"></i>
                </a>
            </li>
        @endif

    </ul>

</nav>
