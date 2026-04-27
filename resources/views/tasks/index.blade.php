@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold text-primary">Agenda de Actividades</h3>
            <p class="text-muted">Gestión de flujo de trabajo hospitalario.</p>
        </div>
        @if(Auth::user()->role === 'supervisor')
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-warning shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Asignar Nueva Tarea
            </button>
        </div>
        @endif
    </div>

    <div class="row">
        @forelse($tasks as $task)
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid {{ $task->category->color ?? '#ccc' }} !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge" style="background-color: {{ $task->category->color ?? '#eee' }}; color: #333;">
                                {{ $task->category->name ?? 'Sin categoría' }}
                            </span>
                            <span class="badge bg-{{ $task->priority == 'alta' ? 'danger' : ($task->priority == 'media' ? 'warning text-dark' : 'info') }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <h5 class="card-title fw-bold">{{ $task->title }}</h5>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-geo-alt-fill me-1"></i> {{ $task->location ?? 'N/A' }} <br>
                            <i class="bi bi-person-fill me-1"></i> Asignado a: <strong>{{ $task->user->name }}</strong>
                        </p>
                        <div class="alert alert-light p-2 mb-2 border-0 small">
                            <i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                            <i class="bi bi-clock ms-2 me-1"></i> {{ $task->due_time ?? 'Sin hora' }}
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pendiente" {{ $task->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_proceso" {{ $task->status == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="completada" {{ $task->status == 'completada' ? 'selected' : '' }}>Completada</option>
                                </select>
                            </form>
                            @if(Auth::user()->role === 'supervisor')
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmTaskDelete({{ $task->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-task-{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-clipboard-check text-muted display-1"></i>
                <p class="text-muted mt-3">No hay tareas asignadas por el momento.</p>
            </div>
        @endforelse
    </div>
</div>

@if(Auth::user()->role === 'supervisor')
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">Asignar Tarea Médica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">¿A quién se asigna?</label>
                        <select name="user_id" class="form-select" required>
                            @foreach($nurses as $nurse)
                                <option value="{{ $nurse->id }}">{{ $nurse->name }} ({{ $nurse->shift }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Título de la Tarea</label>
                        <input type="text" name="title" class="form-control" placeholder="Ej. Aplicar Insulina" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Categoría (Color)</label>
                            <select name="category_id" class="form-select">
                                <option value="">Sin categoría</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Prioridad</label>
                            <select name="priority" class="form-select">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ubicación (Cama / Área)</label>
                        <input type="text" name="location" class="form-control" placeholder="Ej. Cama 104-A">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Fecha</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Hora</label>
                            <input type="time" name="due_time" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold">Asignar Tarea</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    function confirmTaskDelete(id) {
        Swal.fire({
            title: '¿Eliminar tarea?',
            text: "Esta acción quitará la tarea de la agenda de la enfermera.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-task-' + id).submit();
        });
    }
</script>
@endsection