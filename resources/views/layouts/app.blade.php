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
</head>
<body class="bg-light">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="{{ Auth::check() ? route('home') : url('/') }}">
                    <i class="bi bi-calendar-check-fill me-2"></i> {{ config('app.name', 'Agenda Digital') }}
                </a>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Panel</a></li>
                            @if(Auth::user()->role === 'supervisor')
                                <li class="nav-item"><a class="nav-link" href="{{ route('personnel.index') }}">Personal</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categorías</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('tasks.index') }}">Agenda</a></li>
                            @endif
                        @endauth
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
                                <div class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @endauth
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
            // 1. Alerta de Éxito
            @if(session('success'))
                Swal.fire({ 
                    icon: 'success', 
                    title: '¡Operación exitosa!', 
                    text: "{{ session('success') }}", 
                    confirmButtonColor: '#0d6efd' 
                });
            @endif

            // 2. Alerta de Errores de Validación (Ej. Correo ya en uso)
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Hubo un problema',
                    html: `<ul class="text-start" style="list-style:none; padding:0;">
                                @foreach($errors->all() as $error) 
                                    <li class="text-danger mb-1"><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</li> 
                                @endforeach
                           </ul>`,
                    confirmButtonColor: '#dc3545'
                });
            @endif
        });
    </script>
</body>
</html>