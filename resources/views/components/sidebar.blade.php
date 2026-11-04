<ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand-text mx-2">MySchool</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        LMS
    </div>

    <!-- Nav Item - Materi -->
    <li class="nav-item {{ request()->routeIs('materis.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('materis.index') }}">
            <i class="fas fa-fw fa-book-open"></i>
            <span>Materi</span>
        </a>
    </li>

    <!-- Nav Item - Tugas & Ujian -->
    <li class="nav-item {{ request()->routeIs('exams.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('exams.index') }}">
            <i class="fas fa-fw fa-clipboard-list"></i>
            <span>Tugas & Ujian</span>
        </a>
    </li>

    <!-- Nav Item - Jadwal Pengajaran -->
    <li class="nav-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('jadwal.index') }}">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Jadwal Mengajar</span>
        </a>
    </li>

    <!-- Nav Item - Kelas Diajar -->
    <li class="nav-item {{ request()->routeIs('penugasan.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('penugasan.index') }}">
            <i class="fas fa-fw fa-chalkboard"></i>
            <span>Kelas Diajar</span>
        </a>
    </li>

    @if (Auth::check() && Auth::user()->isTeacher())
        <!-- Nav Item - Pelanggaran & Pemanggilan -->
        <li class="nav-item {{ request()->routeIs('pelanggaran.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pelanggaran.index') }}">
                <i class="fas fa-fw fa-exclamation-triangle"></i>
                <span>Pelanggaran Siswa</span>
            </a>
        </li>
    @endif

    @if (Auth::check() && Auth::user()->isSuperAdmin())
        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            Super Admin
        </div>

        <!-- Nav Item - User Management -->
        <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('users.*') ? '' : 'collapsed' }}" href="#"
                data-toggle="collapse" data-target="#collapseUsers"
                aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}" aria-controls="collapseUsers">
                <i class="fas fa-fw fa-users-cog"></i>
                <span>Kelola Akun</span>
            </a>
            <div id="collapseUsers" class="collapse {{ request()->routeIs('users.*') ? 'show' : '' }}"
                aria-labelledby="headingUsers" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Registrasi & Peran:</h6>
                    <a class="collapse-item {{ request()->routeIs('users.index') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <i class="fas fa-list fa-sm mr-1"></i> Daftar Akun
                    </a>
                    <a class="collapse-item {{ request()->routeIs('users.create') ? 'active' : '' }}"
                        href="{{ route('users.create') }}">
                        <i class="fas fa-user-plus fa-sm mr-1"></i> Registrasi Akun
                    </a>
                </div>
            </div>
        </li>

        <!-- Nav Item - Activity Log -->
        <li class="nav-item {{ request()->routeIs('activity.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('activity.index') }}">
                <i class="fas fa-fw fa-history"></i>
                <span>Activity Log</span>
            </a>
        </li>

        <!-- Nav Item - Master Data -->
        <li
            class="nav-item {{ request()->routeIs('siswa.*') || request()->routeIs('guru.*') || request()->routeIs('kelas.*') || request()->routeIs('mapel.*') || request()->routeIs('wali.*') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('siswa.*') || request()->routeIs('guru.*') || request()->routeIs('kelas.*') || request()->routeIs('mapel.*') || request()->routeIs('wali.*') ? '' : 'collapsed' }}"
                href="#" data-toggle="collapse" data-target="#collapseMaster"
                aria-expanded="{{ request()->routeIs('siswa.*') || request()->routeIs('guru.*') || request()->routeIs('kelas.*') || request()->routeIs('mapel.*') || request()->routeIs('wali.*') ? 'true' : 'false' }}"
                aria-controls="collapseMaster">
                <i class="fas fa-fw fa-database"></i>
                <span>Master Data</span>
            </a>
            <div id="collapseMaster"
                class="collapse {{ request()->routeIs('siswa.*') || request()->routeIs('guru.*') || request()->routeIs('kelas.*') || request()->routeIs('mapel.*') || request()->routeIs('wali.*') ? 'show' : '' }}"
                aria-labelledby="headingMaster" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Data Sekolah:</h6>
                    <a class="collapse-item {{ request()->routeIs('siswa.*') ? 'active' : '' }}"
                        href="{{ route('siswa.index') }}">
                        <i class="fas fa-user-graduate fa-sm mr-1"></i> Siswa
                    </a>
                    <a class="collapse-item {{ request()->routeIs('guru.*') ? 'active' : '' }}"
                        href="{{ route('guru.index') }}">
                        <i class="fas fa-chalkboard-teacher fa-sm mr-1"></i> Guru
                    </a>
                    <a class="collapse-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}"
                        href="{{ route('kelas.index') }}">
                        <i class="fas fa-door-open fa-sm mr-1"></i> Kelas
                    </a>
                    <a class="collapse-item {{ request()->routeIs('mapel.*') ? 'active' : '' }}"
                        href="{{ route('mapel.index') }}">
                        <i class="fas fa-book fa-sm mr-1"></i> Mata Pelajaran
                    </a>
                    <a class="collapse-item {{ request()->routeIs('wali.*') ? 'active' : '' }}"
                        href="{{ route('wali.index') }}">
                        <i class="fas fa-user-friends fa-sm mr-1"></i> Wali Murid
                    </a>
                </div>
            </div>
        </li>
    @endif

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Akun Saya
    </div>

    <!-- Nav Item - Notifications -->
    <li class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('notifications.index') }}">
            <i class="fas fa-fw fa-bell"></i>
            <span>Notifikasi Saya</span>
        </a>
    </li>

    <!-- Nav Item - Profile -->
    <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('profile.edit') }}">
            <i class="fas fa-fw fa-user-circle"></i>
            <span>Profil & Password</span>
        </a>
    </li>

    <!-- Nav Item - Report Bug / Request Fitur -->
    <li class="nav-item {{ request()->routeIs('report.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('report.index') }}">
            <i class="fas fa-fw fa-bug"></i>
            <span>Laporkan Bug / Request</span>
        </a>
    </li>


</ul>
