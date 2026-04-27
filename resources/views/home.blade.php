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
                    <h4 class="mb-4">Bienvenido/a, <strong>{{ Auth::user()->name }}</strong></h4>

                    @if(Auth::user()->role === 'supervisor')
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Personal a cargo</h5>
                                    <h2 class="display-5 text-primary">{{ $personalCount }}</h2>
                                    <a href="{{ route('personnel.index') }}" class="btn btn-outline-primary btn-sm mt-2">Gestionar Personal</a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Tareas Pendientes</h5>
                                    <h2 class="display-5 text-warning">{{ $tareasPendientesCount }}</h2>
                                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning btn-sm mt-2">Ver Agenda (Rejilla)</a>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Protocolos</h5>
                                    <h2 class="display-5 text-success">{{ $protocolosCount }}</h2>
                                    <a href="{{ route('templates.index') }}" class="btn btn-outline-success btn-sm mt-2">Administrar Plantillas</a>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-activity text-danger pulse"></i> Balanceo de Cargas (Tiempo Real)</h5>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-0">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-4 py-3">Enfermera/o</th>
                                                    <th>Turno</th>
                                                    <th>Pendientes</th>
                                                    <th>Estatus de Carga</th>
                                                    <th>Avisos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($workload as $nurse)
                                                <tr>
                                                    <td class="ps-4 py-3 fw-bold">{{ $nurse->name }}</td>
                                                    <td><span class="badge bg-secondary">{{ $nurse->shift }}</span></td>
                                                    <td>
                                                        <h5 class="mb-0 fw-bold {{ $nurse->pending_tasks == 0 ? 'text-success' : 'text-dark' }}">
                                                            {{ $nurse->pending_tasks }}
                                                        </h5>
                                                    </td>
                                                    <td style="width: 30%;">
                                                        <div class="progress" style="height: 10px;">
                                                            @php
                                                                $color = 'bg-success';
                                                                if($nurse->workload_percent > 40) $color = 'bg-warning';
                                                                if($nurse->workload_percent > 75) $color = 'bg-danger';
                                                            @endphp
                                                            <div class="progress-bar {{ $color }}" style="width: {{ $nurse->workload_percent }}%"></div>
                                                        </div>
                                                        <small class="text-muted">{{ $nurse->pending_tasks == 0 ? 'Disponible / Libre' : ($nurse->workload_percent >= 100 ? 'Saturada' : 'Ocupada') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($nurse->delayed_tasks > 0)
                                                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> {{ $nurse->delayed_tasks }} Tarea(s) Retrasadas</span>
                                                        @else
                                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> A tiempo</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="5" class="text-center py-4">No hay personal registrado.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Mis Tareas de Hoy</h5>
                                    <h2 class="display-5 text-primary">{{ $misTareasHoyCount }}</h2>
                                    <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-sm mt-2">Ir a mi Agenda</a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm text-center p-3 h-100">
                                    <h5 class="text-muted">Tareas Completadas</h5>
                                    <h2 class="display-5 text-success">{{ $misCompletadasCount }}</h2>
                                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-success btn-sm mt-2">Ver Historial</a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Efecto de latido para iconos urgentes */
    .pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>
@endsection