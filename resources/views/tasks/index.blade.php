@extends('layouts.app')

@section('content')
@php
    $isSupervisor = Auth::user()->isSupervisor();
@endphp

<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h3 class="fw-bold text-primary mb-1">
                <i class="bi bi-calendar2-week me-2"></i> Agenda Hospitalaria
            </h3>
            <p class="text-muted mb-0">
                Gestión de tareas, prioridades, estados y seguimiento del personal de enfermería.
            </p>
        </div>

        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-primary shadow-sm fw-bold me-2">
                <i class="bi bi-calendar-week-fill me-1"></i> Vista calendario
            </a>

            <a href="{{ route('tasks.pdf') }}" class="btn btn-outline-danger shadow-sm fw-bold me-2">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF del turno
            </a>

            <button type="button" class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                <i class="bi bi-plus-circle-fill me-1"></i>
                {{ $isSupervisor ? 'Asignar tarea' : 'Añadir mi tarea' }}
            </button>
        </div>
    </div>

    @if($isSupervisor)

        {{-- Tarjetas de resumen --}}
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-bold">Activas</div>
                        <div class="display-6 fw-bold text-primary">{{ $summary['activas'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-bold">Críticas</div>
                        <div class="display-6 fw-bold text-dark">{{ $summary['criticas'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-bold">Completadas hoy</div>
                        <div class="display-6 fw-bold text-success">{{ $summary['completadas_hoy'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-bold">Reprogramadas</div>
                        <div class="display-6 fw-bold text-warning">{{ $summary['reprogramadas'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-bold">Canceladas</div>
                        <div class="display-6 fw-bold text-secondary">{{ $summary['canceladas'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body text-center">
                        <div class="small fw-bold">Resultado filtro</div>
                        <div class="display-6 fw-bold">{{ $summary['resultado_actual'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros del jefe de piso --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-funnel-fill text-primary me-2"></i> Filtros de supervisión
                    </h5>
                    <small class="text-muted">
                        Por defecto se muestran solo tareas activas para evitar saturar la agenda.
                    </small>
                </div>

                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                    <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                </a>
            </div>

            <div class="card-body bg-light">
                <form method="GET" action="{{ route('tasks.index') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Enfermera/o</label>
                            <select name="nurse_id" class="form-select">
                                <option value="">Todas/os</option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->id }}" {{ request('nurse_id') == $nurse->id ? 'selected' : '' }}>
                                        {{ $nurse->name }} — {{ $nurse->shift ?? 'Sin turno' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="status" class="form-select">
                                <option value="activas" {{ request('status', 'activas') === 'activas' ? 'selected' : '' }}>Activas</option>
                                <option value="todas" {{ request('status') === 'todas' ? 'selected' : '' }}>Todas</option>
                                <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="en_proceso" {{ request('status') === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                                <option value="reprogramada" {{ request('status') === 'reprogramada' ? 'selected' : '' }}>Reprogramada</option>
                                <option value="completada" {{ request('status') === 'completada' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelada" {{ request('status') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Prioridad</label>
                            <select name="priority" class="form-select">
                                <option value="">Todas</option>
                                <option value="critica" {{ request('priority') === 'critica' ? 'selected' : '' }}>Crítica</option>
                                <option value="alta" {{ request('priority') === 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="media" {{ request('priority') === 'media' ? 'selected' : '' }}>Media</option>
                                <option value="baja" {{ request('priority') === 'baja' ? 'selected' : '' }}>Baja</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Categoría</label>
                            <select name="category_id" class="form-select">
                                <option value="">Todas</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Buscar</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Texto...">
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Desde</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-6 mb-3 text-md-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-search me-1"></i> Aplicar filtros
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultados --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-list-check text-primary me-2"></i>
                Tareas encontradas
            </h5>

            <span class="badge bg-primary fs-6">
                {{ $tasks->total() }} resultado(s)
            </span>
        </div>

        <div class="row">
            @forelse($tasks as $task)
                @include('tasks.partials.task-card', ['task' => $task, 'isSupervisor' => true])
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-search display-1 text-muted"></i>
                            <h5 class="fw-bold mt-3">No se encontraron tareas</h5>
                            <p class="text-muted mb-0">
                                Cambia los filtros o limpia la búsqueda para visualizar más registros.
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $tasks->links() }}
        </div>

    @else

        {{-- Vista de enfermería --}}
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <ul class="nav nav-tabs border-0" id="taskTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-primary" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        Activas
                        <span class="badge bg-danger ms-1">{{ $pendingTasks->count() }}</span>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-success" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        Historial
                        <span class="badge bg-success ms-1">{{ $completedTasks->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="taskTabsContent">
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="row">
                    @forelse($pendingTasks as $task)
                        @include('tasks.partials.task-card', ['task' => $task, 'isSupervisor' => false])
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-check-circle display-1 text-success"></i>
                            <p class="text-muted mt-3">No tienes tareas activas.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="tab-pane fade" id="completed" role="tabpanel">
                <div class="row">
                    @forelse($completedTasks as $task)
                        @include('tasks.partials.task-card', ['task' => $task, 'isSupervisor' => false])
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-archive display-1 text-muted"></i>
                            <p class="text-muted mt-3">Aún no hay tareas en historial.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    @endif
</div>

{{-- Modal nueva tarea --}}
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard2-plus me-2"></i>
                    {{ $isSupervisor ? 'Asignar nueva tarea clínica' : 'Añadir tarea personal' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <div class="modal-body bg-light">
                    @if($isSupervisor)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enfermera/o asignado</label>
                            <select name="user_id" id="user_id_select" class="form-select select2-nurse" required style="width: 100%;">
                                <option value=""></option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->id }}">
                                        {{ $nurse->name }} — Turno: {{ $nurse->shift ?? 'Sin turno' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0">
                                            <i class="bi bi-activity text-danger me-1"></i>
                                            Análisis de disponibilidad
                                        </h6>
                                        <small class="text-muted">
                                            Compara la carga del personal antes de asignar la tarea.
                                        </small>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="analyzeAvailability()">
                                        <i class="bi bi-search-heart me-1"></i> Analizar
                                    </button>
                                </div>

                                <div id="availabilityResult" class="mt-3">
                                    <div class="alert alert-light border small mb-0">
                                        Selecciona fecha y hora, luego presiona <strong>Analizar</strong>.
                                        Si no eliges hora, se analizará la carga del día completo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Título de la tarea</label>
                        <input type="text" name="title" class="form-control" placeholder="Ej. Verificar signos vitales" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría</label>
                            <select name="category_id" class="form-select">
                                <option value="">Sin categoría</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prioridad</label>
                            <select name="priority" class="form-select" required>
                                <option value="critica">Crítica</option>
                                <option value="alta">Alta</option>
                                <option value="media" selected>Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" name="due_date" id="due_date_input" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hora</label>
                            <input type="time" name="due_time" id="due_time_input" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Ubicación</label>
                            <input type="text" name="location" class="form-control" placeholder="Ej. Área A / Cama 204">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe la actividad que se debe realizar."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Observaciones iniciales</label>
                        <textarea name="observations" class="form-control" rows="2" placeholder="Indicaciones adicionales, si aplica."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-1"></i> Guardar tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2-nurse').select2({
            dropdownParent: $('#newTaskModal'),
            placeholder: "Selecciona enfermera/o...",
            allowClear: true
        });
    });

    function confirmTaskDelete(id) {
        Swal.fire({
            title: '¿Eliminar tarea?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-task-' + id).submit();
            }
        });
    }
    
    function analyzeAvailability() {
    const dateInput = document.getElementById('due_date_input');
    const timeInput = document.getElementById('due_time_input');
    const resultBox = document.getElementById('availabilityResult');

    if (!dateInput || !resultBox) {
        return;
    }

    const dueDate = dateInput.value;
    const dueTime = timeInput ? timeInput.value : '';

    if (!dueDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha requerida',
            text: 'Selecciona una fecha para analizar disponibilidad.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    resultBox.innerHTML = `
        <div class="alert alert-info small mb-0">
            <i class="bi bi-hourglass-split me-1"></i>
            Analizando disponibilidad del personal...
        </div>
    `;

    const url = `{{ route('tasks.availability') }}?due_date=${encodeURIComponent(dueDate)}&due_time=${encodeURIComponent(dueTime)}`;

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudo consultar la disponibilidad.');
            }

            return response.json();
        })
        .then(data => {
            renderAvailability(data);
        })
        .catch(error => {
            resultBox.innerHTML = `
                <div class="alert alert-danger small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    ${error.message}
                </div>
            `;
        });
}

function renderAvailability(data) {
    const resultBox = document.getElementById('availabilityResult');

    if (!data.nurses || data.nurses.length === 0) {
        resultBox.innerHTML = `
            <div class="alert alert-warning small mb-0">
                No hay personal de enfermería registrado.
            </div>
        `;
        return;
    }

    let recommendedHtml = '';

    if (data.recommended) {
        recommendedHtml = `
            <div class="alert alert-success border-0 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>
                            <i class="bi bi-stars me-1"></i>
                            Recomendación:
                        </strong>
                        ${data.recommended.name}
                        <br>
                        <small>
                            ${data.recommended.message}
                        </small>
                    </div>

                    <button type="button" class="btn btn-sm btn-success fw-bold" onclick="selectRecommendedNurse(${data.recommended.id})">
                        Usar
                    </button>
                </div>
            </div>
        `;
    }

    let rows = '';

    data.nurses.forEach(nurse => {
        rows += `
            <tr>
                <td>
                    <strong>${nurse.name}</strong><br>
                    <small class="text-muted">${nurse.shift}</small>
                </td>

                <td class="text-center">${nurse.pending_tasks}</td>
                <td class="text-center">${nurse.day_tasks}</td>
                <td class="text-center">${nurse.same_time_tasks}</td>
                <td class="text-center">${nurse.critical_tasks}</td>
                <td class="text-center">${nurse.delayed_tasks}</td>

                <td style="min-width: 120px;">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar ${nurse.progress_class}" style="width: ${nurse.workload_percent}%"></div>
                    </div>
                    <small class="text-muted">${nurse.workload_percent}%</small>
                </td>

                <td>
                    <span class="badge ${nurse.badge_class}">
                        ${nurse.load_status}
                    </span>
                </td>

                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectRecommendedNurse(${nurse.id})">
                        Seleccionar
                    </button>
                </td>
            </tr>
        `;
    });

    resultBox.innerHTML = `
        ${recommendedHtml}

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Personal</th>
                        <th class="text-center">Activas</th>
                        <th class="text-center">Día</th>
                        <th class="text-center">Misma hora</th>
                        <th class="text-center">Críticas</th>
                        <th class="text-center">Retrasadas</th>
                        <th>Carga</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    ${rows}
                </tbody>
            </table>
        </div>
    `;
}

function selectRecommendedNurse(nurseId) {
    const select = $('#user_id_select');

    select.val(String(nurseId)).trigger('change');

    Swal.fire({
        icon: 'success',
        title: 'Personal seleccionado',
        text: 'La enfermera/o fue seleccionada para esta tarea.',
        timer: 1300,
        showConfirmButton: false
    });
}
</script>
@endsection