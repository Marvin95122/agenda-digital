@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    {{ __('Panel de Control - Agenda Digital') }}
                </div>

                <div class="card-body bg-light">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4 class="mb-4">Bienvenido/a, <strong>{{ Auth::user()->name }}</strong></h4>

                    @if(Auth::user()->role === 'supervisor')
                        <div class="alert alert-info border-0 shadow-sm">
                            Estás en el modo <strong>Jefe de Piso / Supervisor (Turno {{ Auth::user()->shift }})</strong>.
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Personal a cargo</h5>
                                    <h2 class="display-5 text-primary">{{ $personalCount }}</h2>
                                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-primary btn-sm mt-2">Gestionar Enfermeras/os</a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Tareas Pendientes</h5>
                                    <h2 class="display-5 text-warning">{{ $tareasPendientesCount }}</h2>
                                    <button class="btn btn-outline-warning btn-sm mt-2">Asignar Tareas (Rejilla)</button>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Protocolos</h5>
                                    <h2 class="display-5 text-success">{{ $protocolosCount }}</h2>
                                    <button class="btn btn-outline-success btn-sm mt-2">Crear Plantillas</button>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="alert alert-success border-0 shadow-sm">
                            Estás en el modo <strong>Personal de Enfermería (Turno {{ Auth::user()->shift }})</strong>.
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Mis Tareas de Hoy</h5>
                                    <h2 class="display-5 text-primary">{{ $misTareasHoyCount }}</h2>
                                    <button class="btn btn-primary btn-sm mt-2">Ver mi Agenda</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Tareas Completadas</h5>
                                    <h2 class="display-5 text-success">{{ $misCompletadasCount }}</h2>
                                    <button class="btn btn-outline-success btn-sm mt-2">Ver Historial</button>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection