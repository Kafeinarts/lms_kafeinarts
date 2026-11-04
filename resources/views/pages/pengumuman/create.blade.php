@extends('layouts.app')

@section('title', 'Buat Pengumuman - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Buat Pengumuman</h1>
            <p class="text-muted small mb-0">Publikasikan informasi penting untuk civitas sekolah</p>
        </div>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bullhorn mr-1"></i> Formulir Pengumuman</h6>
                </div>
                <div class="card-body">
                    @include('pages.pengumuman._form')
                </div>
            </div>
        </div>
    </div>
@endsection
