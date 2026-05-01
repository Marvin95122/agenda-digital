@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row align-items-center mb-5">
        <!-- Texto de Bienvenida -->
        <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Agenda Digital
            </h1>
            <h2 class="h4 text-secondary mb-4">Gestión Inteligente de Flujo de Trabajo Hospitalario</h2>
            <p class="lead text-muted mb-4">
                Optimiza la asignación de tareas clínicas, automatiza protocolos médicos y mejora la comunicación entre el equipo de enfermería y supervisión en tiempo real.
            </p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                @auth
                    <!-- Si ya inició sesión, lo manda a su panel -->
                    <a href="{{ url('/home') }}" class="btn btn-primary btn-lg px-4 me-md-2 fw-bold shadow-sm">
                        <i class="bi bi-speedometer2 me-2"></i> Ir a mi Panel de Control
                    </a>
                @else
                    <!-- Si no ha iniciado sesión, lo manda al Login -->
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                    </a>
                @endauth
            </div>
        </div>

        <!-- Tarjetas Decorativas de Características -->
        <div class="col-lg-6">
            <div class="row g-4">
                <div class="col-sm-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm border border-light h-100 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-clipboard2-pulse text-primary fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Gestión de Tareas</h5>
                        <p class="text-muted small mb-0">Organización visual por colores y prioridades.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm border border-light h-100 text-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-lightning-charge text-warning fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Protocolos Automáticos</h5>
                        <p class="text-muted small mb-0">Asigna rutinas médicas completas con un solo clic.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm border border-light h-100 text-center">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-diagram-3 text-success fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Balanceo de Cargas</h5>
                        <p class="text-muted small mb-0">Monitorea en tiempo real quién está libre o saturado.</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm border border-light h-100 text-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-file-earmark-pdf text-danger fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Reportes en PDF</h5>
                        <p class="text-muted small mb-0">Exporta las hojas de ruta para el cambio de turno.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection