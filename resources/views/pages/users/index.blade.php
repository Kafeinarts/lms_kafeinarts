@extends('layouts.app')

@section('title', 'Manajemen Pengguna - StaffWork')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Manajemen Pengguna & Staff</h1>
            <p class="text-muted small mb-0">Kelola akun terdaftar dan daftarkan akun baru dengan role yang ditentukan</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
            <i class="fas fa-user-plus fa-sm text-white-50 mr-1"></i> Registrasi Akun Baru
        </a>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users mr-1"></i> Daftar Seluruh Akun Pengguna
            </h6>
            <span class="badge badge-light border">Total: {{ $users->total() }} Akun</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th style="width: 70px;" class="text-center">Foto</th>
                            <th>Nama Lengkap</th>
                            <th>Alamat Email</th>
                            <th style="width: 130px;" class="text-center">Role / Hak Akses</th>
                            <th style="width: 190px;" class="text-center">Kata Sandi</th>
                            <th style="width: 150px;">Tanggal Daftar</th>
                            <th style="width: 140px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $u)
                            <tr>
                                <td class="text-center align-middle font-weight-bold text-muted">
                                    {{ $users->firstItem() + $index }}
                                </td>
                                <td class="text-center align-middle">
                                    <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}"
                                        class="rounded-circle border"
                                        style="width: 42px; height: 42px; object-fit: cover;">
                                </td>
                                <td class="align-middle font-weight-bold text-gray-900">
                                    {{ $u->name }}
                                    @if ($u->id === Auth::id())
                                        <span class="badge badge-secondary ml-1">Akun Anda</span>
                                    @endif
                                </td>
                                <td class="align-middle text-muted">{{ $u->email }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $u->role_badge_class }} px-2 py-1">
                                        {{ $u->role_name }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    @if ($u->password_plain)
                                        <div class="input-group input-group-sm">
                                            <input type="password" readonly
                                                id="password-{{ $u->id }}"
                                                value="{{ $u->password_plain }}"
                                                class="form-control form-control-sm bg-light text-center font-weight-bold"
                                                style="letter-spacing: 1px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    title="Tampilkan / sembunyikan kata sandi"
                                                    data-password-target="password-{{ $u->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">
                                            <i class="fas fa-lock mr-1"></i>Tidak tersedia
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle small text-muted">
                                    {{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('users.edit', $u) }}" class="btn btn-outline-info" title="Edit Akun & Role">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if ($u->id !== Auth::id())
                                            <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline"
                                                data-confirm='Apakah Anda yakin ingin menghapus akun {{ $u->name }}?'>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus Akun">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada akun pengguna lain.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '[data-password-target]', function () {
        var input = document.getElementById($(this).data('password-target'));
        var icon = $(this).find('i');

        if (!input) {
            return;
        }

        input.type = input.type === 'password' ? 'text' : 'password';
        icon.toggleClass('fa-eye fa-eye-slash');
    });
</script>
@endpush

