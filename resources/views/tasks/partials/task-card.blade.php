@php
    $isClosed = in_array($task->status, ['completada', 'cancelada']);
    $statusIcon = match ($task->status) {
        'pendiente' => 'bi-hourglass-split',
        'en_proceso' => 'bi-play-circle-fill',
        'completada' => 'bi-check-circle-fill',
        'reprogramada' => 'bi-calendar2-week-fill',
        'cancelada' => 'bi-x-circle-fill',
        default => 'bi-circle-fill',
    };
@endphp

<div class="col-md-6 col-lg-4 mb-4">
    <div class="card border-0 shadow-sm h-100 task-card {{ $isClosed ? 'opacity-75' : '' }}"
         style="border-left: 6px solid {{ $task->card_border_class }} !important;">

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge" style="background-color: {{ $task->category->color ?? '#e9ecef' }}; color: #212529;">
                    {{ $task->category->name ?? 'General' }}
                </span>

                <span class="badge {{ $task->priority_badge_class }}">
                    {{ $task->priority_label }}
                </span>
            </div>

            <h5 class="card-title fw-bold mb-2 {{ $isClosed ? 'text-muted text-decoration-line-through' : '' }}">
                {{ $task->title }}
            </h5>

            <div class="mb-3">
                <span class="badge {{ $task->status_badge_class }}">
                    <i class="bi {{ $statusIcon }} me-1"></i> {{ $task->status_label }}
                </span>
            </div>

            <p class="small text-muted mb-2">
                <i class="bi bi-calendar-event me-1 text-primary"></i>
                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}

                <br>

                <i class="bi bi-clock me-1 text-primary"></i>
                {{ $task->due_time ?? 'Sin hora' }}

                <br>

                <i class="bi bi-geo-alt-fill me-1 text-primary"></i>
                {{ $task->location ?? 'Sin ubicación' }}
            </p>

            @if($isSupervisor ?? false)
                <p class="small mb-2">
                    <i class="bi bi-person-badge-fill me-1 text-secondary"></i>
                    Asignada a:
                    <strong>{{ $task->user->name ?? 'Sin asignar' }}</strong>
                </p>
            @else
                @if($task->assignedBy)
                    <p class="small mb-2">
                        <i class="bi bi-person-check-fill me-1 text-secondary"></i>
                        Asignada por:
                        <strong>{{ $task->assignedBy->name }}</strong>
                    </p>
                @endif
            @endif

            @if($task->description)
                <div class="alert alert-light border small py-2 mb-2">
                    <strong>Descripción:</strong><br>
                    {{ $task->description }}
                </div>
            @endif

            @if($task->observations)
                <div class="alert alert-info border-0 small py-2 mb-2">
                    <strong>Observaciones:</strong><br>
                    {!! nl2br(e($task->observations)) !!}
                </div>
            @endif

            @if($task->status === 'reprogramada')
                <div class="alert alert-warning small py-2 mb-2">
                    <strong>Motivo de reprogramación:</strong><br>
                    {{ $task->reschedule_reason ?? 'Sin motivo registrado.' }}
                </div>
            @endif

            @if($task->status === 'cancelada')
                <div class="alert alert-dark small py-2 mb-2">
                    <strong>Motivo de cancelación:</strong><br>
                    {{ $task->cancel_reason ?? 'Sin motivo registrado.' }}
                </div>
            @endif

            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST" class="mt-3">
                @csrf
                @method('PATCH')

                <label class="form-label small fw-bold mb-1">Actualizar estado</label>
                <select name="status" class="form-select form-select-sm mb-2">
                    <option value="pendiente" {{ $task->status === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_proceso" {{ $task->status === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                    <option value="completada" {{ $task->status === 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="reprogramada" {{ $task->status === 'reprogramada' ? 'selected' : '' }}>Reprogramada</option>
                    <option value="cancelada" {{ $task->status === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>

                <input type="text"
                       name="status_note"
                       class="form-control form-control-sm mb-2"
                       placeholder="Motivo u observación del cambio (opcional)">

                <button type="submit" class="btn btn-sm btn-outline-primary w-100 fw-bold">
                    <i class="bi bi-arrow-repeat me-1"></i> Actualizar
                </button>
            </form>
        </div>

        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                @if($task->completed_at)
                    Completada: {{ $task->completed_at->format('d/m/Y H:i') }}
                @elseif($task->cancelled_at)
                    Cancelada: {{ $task->cancelled_at->format('d/m/Y H:i') }}
                @elseif($task->rescheduled_at)
                    Reprogramada: {{ $task->rescheduled_at->format('d/m/Y H:i') }}
                @else
                    Creada: {{ $task->created_at->format('d/m/Y H:i') }}
                @endif
            </small>

            @if(($isSupervisor ?? false) || ($task->assigned_by === null && $task->user_id === Auth::id()))
                <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmTaskDelete({{ $task->id }})">
                    <i class="bi bi-trash"></i>
                </button>

                <form id="delete-task-{{ $task->id }}" action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>