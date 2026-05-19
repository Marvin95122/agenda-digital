<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Agenda Digital') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            background: #f5f7fb;
        }

        .navbar {
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .navbar-brand {
            letter-spacing: -0.3px;
        }

        .nav-link {
            font-weight: 600;
        }

        .nav-link.active,
        .nav-link:hover {
            color: #0d6efd !important;
        }

        .dropdown-menu {
            border-radius: 14px;
        }

        main {
            min-height: calc(100vh - 70px);
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="{{ Auth::check() ? route('home') : url('/') }}">
                    <i class="bi bi-calendar-heart-fill me-2"></i>
                    {{ config('app.name', 'Agenda Digital') }}
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">

                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Panel
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                                    <i class="bi bi-calendar-check me-1"></i> Agenda
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('tasks.calendar') ? 'active' : '' }}" href="{{ route('tasks.calendar') }}">
                                    <i class="bi bi-calendar-week me-1"></i> Calendario
                                </a>
                            </li>

                            @if(Auth::user()->isSupervisor())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}" href="{{ route('personnel.index') }}">
                                        <i class="bi bi-people-fill me-1"></i> Personal
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags-fill me-1"></i> Categorías
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link text-success fw-bold {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">
                                        <i class="bi bi-clipboard2-pulse-fill me-1"></i> Protocolos
                                    </a>
                                </li>
                            @endif
                        @endauth

                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link fw-bold text-primary" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i>
                                    {{ Auth::user()->name }}
                                    <span class="badge bg-primary ms-1">
                                        {{ Auth::user()->role === 'supervisor' ? 'Supervisor' : 'Enfermería' }}
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <h6 class="dropdown-header">
                                        Turno: {{ Auth::user()->shift ?? 'Sin turno' }}
                                    </h6>

                                    <a class="dropdown-item text-danger fw-bold" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
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

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación exitosa!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#0d6efd'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Hubo un problema',
                    html: `<ul class="text-start" style="list-style:none; padding:0;">
                        @foreach($errors->all() as $error)
                            <li class="text-danger mb-1">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ $error }}
                            </li>
                        @endforeach
                    </ul>`,
                    confirmButtonColor: '#dc3545'
                });
            @endif
        });
    </script>
</body>
</html>