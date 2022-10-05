<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://momentjs.com/downloads/moment.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/footer.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">
    <div id="app" class="d-flex flex-column h-100">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo.svg') }}" alt="" width="100%" height="60">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            @guest
                                <a class="nav-link" href="{{ route('/') }}">{{ __('HOME') }}</a>
                            @else
                                <a class="nav-link" href="{{ route('home') }}">{{ __('HOME') }}</a>
                            @endguest
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('about') }}">{{ __('ABOUT') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('offer') }}">{{ __('OFFER') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('job') }}">{{ __('GET JOB') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('support') }}">{{ __('SUPPORT') }}</a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('LOGIN') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="/register/user">{{ __('REGISTER AS USER') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/register/freelancer">{{ __('REGISTER AS FREELANCER') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-5 my-5 flex-shrink-0">
            @yield('content')
        </main>

        <div class="footer-custom mt-auto">
            <div class="container">
                <footer class="d-flex flex-nowrap justify-content-between align-items-center mt-auto">
                    <div class="col-md-4 mb-0 text-muted">
                        <img src="{{ asset('img/logo.svg') }}" alt="" width="100%" height="60">
                    </div>  
                    <div class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
                        <a href="/" class="mb-3 me-2 mb-md-0 text-muted text-decoration-none lh-1">
                            <svg class="bi" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
                        </a>
                        <span class="text-muted">© 2022 All right reserved. {{ config('app.name', 'Laravel') }}</span>
                    </div>

                    <ul class="nav col-md-4 justify-content-end">
                        <li class="ms-3"><a class="text-muted" href="#"><img src="{{ asset('img/facebook.svg') }}"></a></li>
                        <li class="ms-3"><a class="text-muted" href="#"><img src="{{ asset('img/instagram.svg') }}"></a></li>
                        <li class="ms-3"><a class="text-muted" href="#"><img src="{{ asset('img/phone.svg') }}"></a></li>
                    </ul>
                </footer>
            </div>
        </div>

        </div>
        <noscript>
        Javascript is not enabled in your browser, you'll be redirected to another page.
        <meta HTTP-EQUIV="REFRESH" content="0; url=/404"> 
        </noscript>
    </body>
</html>