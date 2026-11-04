@php
    $user = Auth::user();
@endphp

<!-- Navigasi bawah khusus mobile -->
<nav class="mobile-bottom-nav" aria-label="Navigasi utama mobile">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('materis.index') }}" class="{{ request()->routeIs('materis.*') ? 'is-active' : '' }}">
        <i class="fas fa-book-open"></i>
        <span>Materi</span>
    </a>
    <a href="{{ route('exams.index') }}" class="{{ request()->routeIs('exams.*') ? 'is-active' : '' }}">
        <i class="fas fa-clipboard-list"></i>
        <span>Tugas</span>
    </a>
    <a href="{{ route('jadwal.index') }}" class="{{ request()->routeIs('jadwal.*') ? 'is-active' : '' }}">
        <i class="fas fa-calendar-alt"></i>
        <span>Jadwal</span>
    </a>
    <button type="button" data-toggle="modal" data-target="#mobileMenuSheet">
        <i class="fas fa-ellipsis-h"></i>
        <span>Menu</span>
    </button>
</nav>

<!-- Bottom sheet menu lengkap -->
<div class="modal fade mobile-nav-sheet" id="mobileMenuSheet" tabindex="-1" role="dialog"
    aria-labelledby="mobileMenuSheetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title font-weight-bold text-gray-900 mb-0" id="mobileMenuSheetLabel">Menu Cepat</h6>
                    @if ($user)
                        <div class="small text-muted">{{ $user->name }} &bull; {{ $user->role_name }}</div>
                    @endif
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-tachometer-alt mr-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('materis.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-book-open mr-2 text-primary"></i> Materi Pembelajaran
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('exams.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-clipboard-list mr-2 text-primary"></i> Tugas &amp; Ujian
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('jadwal.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-calendar-alt mr-2 text-primary"></i> Jadwal Mengajar
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('penugasan.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-chalkboard mr-2 text-primary"></i> Kelas Diajar
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('notifications.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-bell mr-2 text-primary"></i> Notifikasi
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('report.index') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-bug mr-2 text-warning"></i> Laporkan Bug / Request
                        </a>
                    </li>
                    <li class="list-group-item p-0 mb-1">
                        <a href="{{ route('profile.edit') }}" class="d-flex align-items-center text-gray-800">
                            <i class="fas fa-fw fa-user-circle mr-2 text-primary"></i> Profil &amp; Password
                        </a>
                    </li>

                    @if ($user && $user->isTeacher())
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('pelanggaran.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-exclamation-triangle mr-2 text-warning"></i> Pelanggaran Siswa
                            </a>
                        </li>
                    @endif

                    @if ($user && $user->isSuperAdmin())
                        <li class="list-group-item p-0 mb-1 mt-2 pt-2 border-top">
                            <a href="{{ route('users.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-users-cog mr-2 text-danger"></i> Kelola Akun
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('activity.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-history mr-2 text-danger"></i> Activity Log
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('siswa.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-user-graduate mr-2 text-danger"></i> Data Siswa
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('guru.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-chalkboard-teacher mr-2 text-danger"></i> Data Guru
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('kelas.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-door-open mr-2 text-danger"></i> Data Kelas
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('mapel.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-book mr-2 text-danger"></i> Mata Pelajaran
                            </a>
                        </li>
                        <li class="list-group-item p-0 mb-1">
                            <a href="{{ route('wali.index') }}" class="d-flex align-items-center text-gray-800">
                                <i class="fas fa-fw fa-user-friends mr-2 text-danger"></i> Wali Murid
                            </a>
                        </li>
                    @endif

                    <li class="list-group-item p-0 mt-2 pt-2 border-top">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-danger font-weight-bold text-decoration-none">
                                <i class="fas fa-fw fa-sign-out-alt mr-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
