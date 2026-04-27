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

    @if(Auth::user()->role === 'supervisor')
        <div class="row">
            @forelse($tasks as $task)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 {{ $task->status == 'completada' ? 'opacity-75 bg-light' : '' }}" style="border-left: 5px solid {{ $task->category->color ?? '#ccc' }} !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge" style="background-color: {{ $task->category->color ?? '#eee' }}; color: #333;">{{ $task->category->name ?? 'Sin categoría' }}</span>
                                <span class="badge bg-{{ $task->priority == 'alta' ? 'danger' : ($task->priority == 'media' ? 'warning text-dark' : 'info') }}">{{ ucfirst($task->priority) }}</span>
                            </div>
                            <h5 class="card-title fw-bold text-{{ $task->status == 'completada' ? 'muted text-decoration-line-through' : 'dark' }}">{{ $task->title }}</h5>
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
                                <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmTaskDelete({{ $task->id }})"><i class="bi bi-trash"></i></button>
                                <form id="delete-task-{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5"><p class="text-muted mt-3">No hay tareas asignadas.</p></div>
            @endforelse
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <ul class="nav nav-tabs border-0" id="taskTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-primary" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        Pendientes <span class="badge bg-danger ms-1">{{ $pendingTasks->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-success" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        Historial <span class="badge bg-success ms-1">{{ $completedTasks->count() }}</span>
                    </button>
                </li>
            </ul>
            <button class="btn btn-sm btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#myTaskModal">
                <i class="bi bi-plus-circle me-1"></i> Añadir Mi Tarea
            </button>
        </div>

        <div class="tab-content" id="taskTabsContent">
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="row">
                    @forelse($pendingTasks as $task)
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid {{ $task->category->color ?? '#ccc' }} !important;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge" style="background-color: {{ $task->category->color ?? '#eee' }}; color: #333;">{{ $task->category->name ?? 'General' }}</span>
                                        <span class="badge bg-{{ $task->priority == 'alta' ? 'danger' : ($task->priority == 'media' ? 'warning text-dark' : 'info') }}">{{ ucfirst($task->priority) }}</span>
                                    </div>
                                    <h5 class="card-title fw-bold">{{ $task->title }}
                                        @if($task->assigned_by == Auth::id()) <span class="badge bg-light text-secondary ms-2 border"><i class="bi bi-person"></i> Personal</span> @endif
                                    </h5>
                                    <p class="card-text text-muted small mb-2"><i class="bi bi-geo-alt-fill me-1 text-primary"></i> Ubicación: <strong>{{ $task->location ?? 'N/A' }}</strong></p>
                                    <div class="alert alert-warning p-2 mb-3 border-0 small"><i class="bi bi-clock me-1"></i> Hora programada: <strong>{{ $task->due_time ?? 'Sin hora' }}</strong></div>
                                    <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">Estado:</span>
                                            <select name="status" class="form-select font-weight-bold" onchange="this.form.submit()">
                                                <option value="pendiente" {{ $task->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="en_proceso" {{ $task->status == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                                <option value="completada">✔ Marcar como Completada</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5"><p class="text-muted">No tienes tareas pendientes.</p></div>
                    @endforelse
                </div>
            </div>

            <div class="tab-pane fade" id="completed" role="tabpanel">
                <div class="row">
                    @forelse($completedTasks as $task)
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm h-100 bg-light opacity-75" style="border-left: 5px solid #198754 !important;">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-muted text-decoration-line-through">{{ $task->title }}</h5>
                                    <p class="small text-success fw-bold mb-3"><i class="bi bi-calendar-check me-1"></i> Completada el {{ $task->updated_at->format('d/m/Y h:i A') }}</p>
                                    <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="completada" selected>Completada</option>
                                            <option value="pendiente">↻ Deshacer</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5"><p class="text-muted">Aún no tienes tareas completadas.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="modal fade" id="myTaskModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Mi Nueva Tarea</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}"> <div class="modal-body">
                            <div class="mb-3"><label class="form-label fw-bold small">Título</label><input type="text" name="title" class="form-control" required></div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small">Categoría</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Ninguna</option>
                                        @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small">Prioridad</label>
                                    <select name="priority" class="form-select">
                                        <option value="baja">Baja</option><option value="media" selected>Media</option><option value="alta">Alta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3"><label class="form-label fw-bold small">Ubicación</label><input type="text" name="location" class="form-control"></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label fw-bold small">Fecha</label><input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label fw-bold small">Hora</label><input type="time" name="due_time" class="form-control"></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary fw-bold w-100">Guardar Mi Tarea</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function confirmTaskDelete(id) {
        Swal.fire({ title: '¿Eliminar tarea?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Sí' }).then((r) => {
            if (r.isConfirmed) document.getElementById('delete-task-' + id).submit();
        });
    }
</script>
@endsection