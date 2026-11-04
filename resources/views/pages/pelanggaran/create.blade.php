@extends('layouts.app')

@section('title', 'Catat Pelanggaran Siswa - StaffWork')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Catat Pelanggaran Siswa</h1>
            <p class="text-muted small mb-0">Siswa yang bersangkutan akan menerima notifikasi pemanggilan</p>
        </div>
        <a href="{{ route('pelanggaran.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Riwayat
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Formulir Pelanggaran
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pelanggaran.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold text-gray-700">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required onchange="fetchStudents(this.value)">
                                <option value="" disabled {{ old('kelas_id') ? '' : 'selected' }}>-- Pilih Kelas --</option>
                                @foreach ($kelasOptions as $kelasId => $kelasName)
                                    <option value="{{ $kelasId }}" {{ (string) old('kelas_id') === (string) $kelasId ? 'selected' : '' }}>
                                        {{ $kelasName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <script>
                            function fetchStudents(kelasId) {
                                $.ajax({
                                    url: '{{ route("pelanggaran.create") }}',
                                    data: { kelas_id: kelasId },
                                    success: function(html) {
                                        $('#student_id').html(html);
                                    }
                                });
                            }
                        </script>

                        <div class="form-group">
                            <label for="student_id" class="font-weight-bold text-gray-700">Siswa <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('student_id') ? '' : 'selected' }}>-- Pilih Siswa --</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" {{ (string) old('student_id') === (string) $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} (NIS: {{ $student->nis ?: '-' }}{{ $student->kelas ? ', '.$student->kelas->name : '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="font-weight-bold text-gray-700">Keterangan Pelanggaran <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Tulis keterangan pelanggaran siswa..."
                                required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="points" class="font-weight-bold text-gray-700">Poin Pelanggaran</label>
                            <input type="number" name="points" id="points" value="{{ old('points', 0) }}"
                                min="0" max="1000"
                                class="form-control @error('points') is-invalid @enderror">
                            <small class="form-text text-muted">Poin akan diakumulasikan pada modul Bimbingan Konseling.</small>
                            @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold text-gray-700">Tujuan Pemanggilan <span class="text-danger">*</span></label>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="roomBk" name="room" value="bk"
                                    class="custom-control-input @error('room') is-invalid @enderror"
                                    {{ old('room', 'bk') === 'bk' ? 'checked' : '' }} required>
                                <label class="custom-control-label" for="roomBk">
                                    <span class="badge badge-danger px-2 py-1 mr-1">Ruang BK</span>
                                    <span class="small text-muted">Panggil siswa ke ruang BK (bimbingan konseling)</span>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="roomGuru" name="room" value="guru"
                                    class="custom-control-input @error('room') is-invalid @enderror"
                                    {{ old('room') === 'guru' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="roomGuru">
                                    <span class="badge badge-warning px-2 py-1 mr-1">Ruang Guru</span>
                                    <span class="small text-muted">Panggil siswa ke ruang guru/wali kelas</span>
                                </label>
                            </div>
                            @error('room')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('pelanggaran.index') }}" class="btn btn-secondary mr-2">Batal</a>
                            <button type="submit" class="btn btn-danger font-weight-bold shadow-sm">
                                <i class="fas fa-bell mr-1"></i> Simpan & Panggil Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
