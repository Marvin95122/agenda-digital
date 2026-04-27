@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold text-success">Protocolos y Plantillas</h3>
            <p class="text-muted">Crea grupos de tareas frecuentes para asignarlas con un solo clic.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-success shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newTemplateModal">
                <i class="bi bi-file-earmark-plus-fill me-1"></i> Crear Protocolo
            </button>
        </div>
    </div>

    <div class="row">
        @forelse($templates as $template)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between">
                        <h5 class="fw-bold text-success mb-0"><i class="bi bi-layers-fill me-2"></i>{{ $template->name }}</h5>
                        <form action="{{ route('templates.destroy', $template->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="button" class="btn-close" onclick="confirmDelete({{ $template->id }})"></button>
                        </form>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Este protocolo generará las siguientes <strong>{{ count($template->tasks_json) }}</strong> tareas:</p>
                        <ul class="list-group list-group-flush mb-4 shadow-sm rounded">
                            @foreach($template->tasks_json as $task)
                                @php
                                    $catName = collect($categories)->firstWhere('id', $task['category_id'])->name ?? 'Gral';
                                    $catColor = collect($categories)->firstWhere('id', $task['category_id'])->color ?? '#ccc';
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center border-0 mb-1" style="border-left: 4px solid {{ $catColor }} !important; background-color: #f8f9fa;">
                                    <span class="small fw-bold">{{ $task['title'] }}</span>
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $catName }} / {{ ucfirst($task['priority']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        
                        <button class="btn btn-outline-success w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#applyTemplate{{ $template->id }}">
                            <i class="bi bi-lightning-charge-fill text-warning"></i> Asignar este Protocolo a una Enfermera
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="applyTemplate{{ $template->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold">Asignar Protocolo: {{ $template->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('templates.apply', $template->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-light border-success border-start border-4 small mb-3">
                                    Se crearán <strong>{{ count($template->tasks_json) }} tareas</strong> nuevas.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">¿A qué enfermera se le asigna?</label>
                                    <select name="user_id" class="form-select select2-nurse" style="width: 100%;" required>
                                        <option value=""></option> @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->id }}">{{ $nurse->name }} (Turno: {{ $nurse->shift }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Ubicación / Cama del Paciente</label>
                                    <input type="text" name="location" class="form-control" placeholder="Ej. Cama 204">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Fecha</label>
                                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Hora (Opcional)</label>
                                        <input type="time" name="due_time" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success fw-bold">Aplicar Protocolo Ahora</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-stack text-muted display-1"></i>
                <p class="text-muted mt-3">No has creado plantillas de tareas frecuentes.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="newTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Crear Nueva Plantilla / Protocolo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('templates.store') }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nombre del Protocolo</label>
                        <input type="text" name="name" class="form-control form-control-lg" required>
                    </div>
                    <p class="fw-bold mb-2 text-muted">Configura las tareas (Llena solo las que necesites):</p>
                    @for($i = 0; $i < 5; $i++)
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <input type="text" name="task_titles[]" class="form-control form-control-sm" placeholder="Título Tarea {{ $i+1 }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="category_ids[]" class="form-select form-select-sm">
                                        <option value="">Categoría...</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="priorities[]" class="form-select form-select-sm">
                                        <option value="alta">P. Alta</option>
                                        <option value="media" selected>P. Media</option>
                                        <option value="baja">P. Baja</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Guardar Protocolo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Convierte los select en buscadores inteligentes
        $('.select2-nurse').each(function() {
            $(this).select2({
                dropdownParent: $(this).closest('.modal'), // Asegura que funcione dentro de la ventanita
                placeholder: "🔍 Busca por nombre...",
                allowClear: true
            });
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: '¿Eliminar protocolo?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-task-form').submit();
        });
    }
</script>
@endsection