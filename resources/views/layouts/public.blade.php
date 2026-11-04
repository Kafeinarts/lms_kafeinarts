<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('meta_description', 'Profil resmi sekolah')">
    <meta name="theme-color" content="#1b2a4d">

    <title>@yield('title', 'Profil Sekolah')</title>

    <link href="{{ asset('template/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800&display=swap" rel="stylesheet">

    <link href="{{ asset('template/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('template/css/app-ui.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body class="public-page">

    <header class="public-topbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="public-brand" href="{{ route('sekolah.public') }}">
                @if (isset($school) && $school->logo_url)
                    <img src="{{ $school->logo_url }}" alt="Logo {{ $school->name }}">
                @else
                    <span class="public-brand-icon"><i class="fas fa-graduation-cap"></i></span>
                @endif
                <span>{{ isset($school) ? $school->name : 'MySchool' }}</span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary font-weight-bold">
                <i class="fas fa-sign-in-alt fa-sm mr-1"></i> Masuk
            </a>
        </div>
    </header>

    <main class="public-main">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="container d-flex flex-wrap align-items-center justify-content-between">
            <span>&copy; {{ date('Y') }} {{ isset($school) ? $school->name : 'MySchool' }}. Profil sekolah.</span>
            <a href="{{ route('login') }}" class="text-muted">Area Staf <i class="fas fa-arrow-right fa-xs ml-1"></i></a>
        </div>
    </footer>

    <script src="{{ asset('template/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
