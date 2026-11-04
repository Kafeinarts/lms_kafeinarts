<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - StaffWork</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        .bg-login-illustration {
            background-color: #f8f9fc;
            background-image: url('{{ asset('template/img/undraw_posting_photo.svg') }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 70%;
        }
    </style>
</head>

<body class="bg-gradient-primary d-flex align-items-center min-vh-100 py-4">

    <div class="container my-auto">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-3">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-illustration"></div>
                            <div class="col-lg-6">
                                <div class="p-4 p-md-5">
                                    <div class="text-center mb-4">
                                        <div class="sidebar-brand-icon rotate-n-15 text-primary mb-2"
                                            style="font-size: 2.2rem;">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <h1 class="h4 text-gray-900 font-weight-bold">StaffWork</h1>
                                        <p class="text-muted small">Silakan masuk menggunakan akun terdaftar</p>
                                    </div>

                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show small"
                                            role="alert">
                                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show small"
                                            role="alert">
                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show small"
                                            role="alert">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            @if ($errors->has('email'))
                                                {{ $errors->first('email') }}
                                            @elseif ($errors->has('password'))
                                                {{ $errors->first('password') }}
                                            @else
                                                Harap periksa kembali form login Anda.
                                            @endif
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="form-group">
                                            <input type="email" name="email"
                                                class="form-control form-control-user @error('email') is-invalid @enderror"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                value="{{ old('email') }}" placeholder="Alamat Email..." required
                                                autofocus>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password"
                                                class="form-control form-control-user @error('password') is-invalid @enderror"
                                                id="exampleInputPassword" placeholder="Kata Sandi..." required>
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" name="remember" class="custom-control-input"
                                                    id="customCheck" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="customCheck">Ingat Saya</label>
                                            </div>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary btn-user btn-block font-weight-bold">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk (Login)
                                        </button>
                                    </form>

                                    <hr>

                                    {{--                                     
                                    <div class="text-center">
                                        <span class="small text-muted">Belum memiliki akun?</span>
                                        <a class="small font-weight-bold ml-1" href="{{ route('register') }}">Buat Akun Baru!</a>
                                    </div> 
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('template/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('template/js/sb-admin-2.min.js') }}"></script>

</body>

</html>
