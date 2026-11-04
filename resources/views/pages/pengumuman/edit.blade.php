@extends('layouts.app')

@section('title', 'Edit Pengumuman - StaffWork')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Edit Pengumuman</h1>
            <p class="text-muted small mb-0">Perbarui isi atau target pembaca pengumuman</p>
        </div>
        <a href="{{ route('pengumuman.show', $announcement) }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit mr-1"></i> Formulir Pengumuman</h6>
                </div>
                <div class="card-body">
                    @include('pages.pengumuman._form')
                </div>
            </div>
        </div>
    </div>
@endsection
