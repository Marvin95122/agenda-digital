@extends('layouts.app')

@section('content')
@php
    $isSupervisor = Auth::user()->isSupervisor();
@endphp

<div class="container">

    {{-- Encabezado --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="dashboard-icon">
                    <i class="bi {{ $isSupervisor ? 'bi-hospital-fill' : 'bi-person-heart' }}"></i>
                </div>

                <div>
                    <h2 class="fw-bold mb-1">
                        {{ $isSupervisor ? 'Centro de Control del Jefe de Piso' : 'Mi turno de enfermería' }}
                    </h2>

                    <p class="text-muted mb-0">
                        Bienvenido/a, <strong>{{ Auth::user()->name }}</strong>.
                        {{ $isSupervisor ? 'Supervisa tareas, carga de trabajo y prioridades del equipo.' : 'Consulta tus actividades, prioridades y avance del día.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-primary fs-6 px-3 py-2">
                <i class="bi bi-clock me-1"></i>
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    @if($isSupervisor)

        {{-- Tarjetas principales supervisor --}}
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <a href="{{ route('personnel.index') }}" class="text-decoration-none">
                    <div class="card metric-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted fw-bold small mb-1">Personal a cargo</p>
                                    <h2 class="fw-bold text-primary mb-0">{{ $personalCount }}</h2>
                                </div>
                                <div class="metric-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <a href="{{ route('tasks.index', ['status' => 'activas']) }}" class="text-decoration-none">
                    <div class="card metric-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted fw-bold small mb-1">Tareas activas</p>
                                    <h2 class="fw-bold text-warning mb-0">{{ $tareasActivasCount }}</h2>
                                </div>
                                <div class="metric-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-list-task"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <a href="{{ route('tasks.index', ['status' => 'activas', 'priority' => 'critica']) }}" class="text-decoration-none">
                    <div class="card metric-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted fw-bold small mb-1">Tareas críticas</p>
                                    <h2 class="fw-bold text-dark mb-0">{{ $tareasCriticasCount }}</h2>
                                </div>
                                <div class="metric-icon bg-dark text-white">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <a href="{{ route('templates.index') }}" class="text-decoration-none">
                    <div class="card metric-card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted fw-bold small mb-1">Protocolos</p>
                                    <h2 class="fw-bold text-success mb-0">{{ $protocolosCount }}</h2>
                                </div>
                                <div class="metric-icon bg-success-subtle text-success">
                                    <i class="bi bi-clipboard2-pulse-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Segunda fila de indicadores --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100 stat-strip">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fw-bold small mb-1">Completadas hoy</p>
                            <h3 class="fw-bold text-success mb-0">{{ $tareasCompletadasHoyCount }}</h3>
                        </div>
                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100 stat-strip">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fw-bold small mb-1">Alta prioridad / Críticas</p>
                            <h3 class="fw-bold text-danger mb-0">{{ $tareasAltaPrioridadCount }}</h3>
                        </div>
                        <i class="bi bi-lightning-charge-fill text-danger fs-1"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100 stat-strip">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted fw-bold small mb-1">Tareas retrasadas</p>
                            <h3 class="fw-bold text-danger mb-0">{{ $tareasRetrasadasCount }}</h3>
                        </div>
                        <i class="bi bi-alarm-fill text-danger fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recomendación y tareas urgentes --}}
        <div class="row mb-4">
            <div class="col-lg-5 mb-3">
                <div class="card border-0 shadow-sm h-100 recommendation-card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-stars text-warning me-2"></i>
                            Recomendación de asignación
                        </h5>

                        @if($recommendedNurse)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($recommendedNurse->name, 0, 1)) }}
                                </div>

                                <div>
                                    <h5 class="fw-bold mb-0">{{ $recommendedNurse->name }}</h5>
                                    <small class="text-muted">
                                        Turno: {{ $recommendedNurse->shift ?? 'Sin turno' }}
                                    </small>
                                </div>
                            </div>

                            <p class="text-muted mb-3">
                                Se recomienda considerar a este personal para nuevas tareas, ya que presenta menor carga relativa según tareas activas, críticas y retrasadas.
                            </p>

                            <div class="progress mb-2" style="height: 12px;">
                                <div class="progress-bar {{ $recommendedNurse->progress_class }}" style="width: {{ $recommendedNurse->workload_percent }}%"></div>
                            </div>

                            <div class="d-flex justify-content-between small text-muted">
                                <span>{{ $recommendedNurse->pending_tasks }} tareas activas</span>
                                <span>{{ $recommendedNurse->workload_percent }}% carga</span>
                            </div>

                            <a href="{{ route('tasks.index', ['nurse_id' => $recommendedNurse->id]) }}" class="btn btn-primary w-100 fw-bold mt-3">
                                <i class="bi bi-person-check-fill me-1"></i>
                                Ver agenda de esta enfermera/o
                            </a>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-person-x display-4 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">No hay personal registrado para recomendar.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-exclamation-diamond-fill text-danger me-2"></i>
                                Prioridades del turno
                            </h5>
                            <small class="text-muted">Tareas críticas y de alta prioridad activas.</small>
                        </div>

                        <a href="{{ route('tasks.index', ['status' => 'activas', 'priority' => 'critica']) }}" class="btn btn-sm btn-outline-danger fw-bold">
                            Ver críticas
                        </a>
                    </div>

                    <div class="card-body pt-0">
                        @forelse($urgentTasks as $task)
                            <div class="urgent-item d-flex justify-content-between align-items-start py-3 border-bottom">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge {{ $task->priority_badge_class }}">{{ $task->priority_label }}</span>
                                        <span class="badge {{ $task->status_badge_class }}">{{ $task->status_label }}</span>
                                    </div>

                                    <h6 class="fw-bold mb-1">{{ $task->title }}</h6>

                                    <small class="text-muted">
                                        <i class="bi bi-person-badge me-1"></i>
                                        {{ $task->user->name ?? 'Sin asignar' }}
                                        ·
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                                        {{ $task->due_time ?? '' }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-check2-circle display-4 text-success"></i>
                                <p class="text-muted mt-3 mb-0">No hay tareas críticas o de alta prioridad activas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Balanceo de cargas --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-activity text-danger me-2"></i>
                        Balanceo de cargas del personal
                    </h5>
                    <small class="text-muted">Vista para apoyar la asignación equilibrada de actividades.</small>
                </div>

                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Asignar tarea
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Personal</th>
                                <th>Turno</th>
                                <th>Activas</th>
                                <th>Críticas</th>
                                <th>Retrasadas</th>
                                <th style="width: 30%;">Carga</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($workload as $nurse)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="mini-avatar">
                                                {{ strtoupper(substr($nurse->name, 0, 1)) }}
                                            </div>

                                            <div>
                                                <div class="fw-bold">{{ $nurse->name }}</div>
                                                <small class="text-muted">Enfermería</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $nurse->shift ?? 'Sin turno' }}
                                        </span>
                                    </td>

                                    <td class="fw-bold">{{ $nurse->pending_tasks }}</td>

                                    <td>
                                        @if($nurse->critical_tasks > 0)
                                            <span class="badge bg-dark">{{ $nurse->critical_tasks }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($nurse->delayed_tasks > 0)
                                            <span class="badge bg-danger">{{ $nurse->delayed_tasks }}</span>
                                        @else
                                            <span class="badge bg-success">0</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar {{ $nurse->progress_class }}" style="width: {{ $nurse->workload_percent }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $nurse->workload_percent }}% de carga estimada</small>
                                    </td>

                                    <td>
                                        <span class="badge {{ $nurse->load_badge }}">
                                            {{ $nurse->load_status }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <a href="{{ route('tasks.index', ['nurse_id' => $nurse->id]) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                            Ver agenda
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-person-x display-5 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">No hay personal de enfermería registrado.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else

        {{-- Panel de enfermería --}}
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="card metric-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <p class="text-muted fw-bold small mb-1">Activas hoy</p>
                        <h2 class="fw-bold text-primary mb-0">{{ $misTareasHoyCount }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card metric-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <p class="text-muted fw-bold small mb-1">Completadas hoy</p>
                        <h2 class="fw-bold text-success mb-0">{{ $misCompletadasHoyCount }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card metric-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <p class="text-muted fw-bold small mb-1">Urgentes</p>
                        <h2 class="fw-bold text-danger mb-0">{{ $misUrgentesCount }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <div class="card metric-card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <p class="text-muted fw-bold small mb-1">Retrasadas</p>
                        <h2 class="fw-bold text-danger mb-0">{{ $misRetrasadasCount }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progreso del turno --}}
        <div class="row mb-4">
            <div class="col-lg-5 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                            Avance del turno
                        </h5>

                        <div class="progress mb-2" style="height: 16px;">
                            <div class="progress-bar bg-success" style="width: {{ $progresoHoy }}%">
                                {{ $progresoHoy }}%
                            </div>
                        </div>

                        <p class="text-muted mb-0">
                            Has completado {{ $misCompletadasHoyCount }} tarea(s) de las registradas para hoy.
                        </p>

                        <a href="{{ route('tasks.index') }}" class="btn btn-primary w-100 fw-bold mt-3">
                            <i class="bi bi-calendar-check me-1"></i> Ir a mi agenda
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-clock-history text-warning me-2"></i>
                            Próxima tarea
                        </h5>

                        @if($proximaTarea)
                            <div class="p-3 rounded bg-light border">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge {{ $proximaTarea->priority_badge_class }}">{{ $proximaTarea->priority_label }}</span>
                                    <span class="badge {{ $proximaTarea->status_badge_class }}">{{ $proximaTarea->status_label }}</span>
                                </div>

                                <h5 class="fw-bold mb-1">{{ $proximaTarea->title }}</h5>

                                <p class="text-muted mb-1">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $proximaTarea->due_time ?? 'Sin hora' }}
                                    ·
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $proximaTarea->location ?? 'Sin ubicación' }}
                                </p>

                                @if($proximaTarea->assignedBy)
                                    <small class="text-muted">
                                        Asignada por: {{ $proximaTarea->assignedBy->name }}
                                    </small>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-check2-circle display-4 text-success"></i>
                                <p class="text-muted mt-3 mb-0">No tienes próximas tareas programadas para hoy.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Agenda de hoy --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-calendar-day text-primary me-2"></i>
                    Agenda de hoy
                </h5>
                <small class="text-muted">Actividades programadas para tu turno actual.</small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Hora</th>
                                <th>Tarea</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($tareasHoy as $task)
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        {{ $task->due_time ?? 'Sin hora' }}
                                    </td>

                                    <td>
                                        <div class="fw-bold">{{ $task->title }}</div>
                                        <small class="text-muted">
                                            {{ $task->category->name ?? 'General' }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge {{ $task->priority_badge_class }}">
                                            {{ $task->priority_label }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge {{ $task->status_badge_class }}">
                                            {{ $task->status_label }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $task->location ?? 'Sin ubicación' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-calendar-x display-5 text-muted"></i>
                                        <p class="text-muted mt-3 mb-0">No hay tareas registradas para hoy.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif
</div>

<style>
    .dashboard-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: linear-gradient(135deg, #0d6efd, #5aa0ff);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.25);
    }

    .metric-card {
        transition: transform .18s ease, box-shadow .18s ease;
        border-radius: 18px;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12) !important;
    }

    .metric-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
    }

    .stat-strip,
    .recommendation-card {
        border-radius: 18px;
    }

    .avatar-circle {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: linear-gradient(135deg, #198754, #5dd39e);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 800;
    }

    .mini-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #e7f1ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .urgent-item:last-child {
        border-bottom: 0 !important;
    }

    .table > :not(caption) > * > * {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
</style>
@endsection