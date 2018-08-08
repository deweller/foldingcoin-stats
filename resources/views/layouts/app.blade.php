<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Merged Folding'))</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img class="navbar-brand__logo" src="/images/fldc-logo.jpg" alt="MergedFolding Logo">
                    {{ config('app.name', 'Merged Folding') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link navbar__nav-link--strong" href="{{ route('teams.index') }}"><i class="fas fa-xs fa-people-carry"></i> {{ __('Teams') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link navbar__nav-link--strong" href="{{ route('members.index') }}"><i class="fas fa-xs fa-user"></i> {{ __('Members') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link navbar__nav-link--strong" target="_blank" href="https://foldingcoin.net"><i class="fas fa-xs fa-link"></i> {{ __('About FoldingCoin') }}</a>
                        </li>

                    {{-- 
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link navbar__nav-link--strong" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link navbar__nav-link--strong" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link navbar__nav-link--strong dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                     --}}
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>


    </div>

    @section('footer')
    <footer class="footer">
        @include('layouts.footer')
    </footer>
    @show

    @section('page_scripts')
    {{-- <script>window.userUuid = {!! json_encode(Auth::user() ? Auth::user()->uuid : null); !!}; </script> --}}
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    @show
    </body>
</html>
