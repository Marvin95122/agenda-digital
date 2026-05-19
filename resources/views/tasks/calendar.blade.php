@extends('layouts.app')

@section('content')
@php
    $isSupervisor = Auth::user()->isSupervisor();
@endphp

<div class="container-fluid px-3 px-md-4">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-7">
            <h3 class="fw-bold text-primary mb-1">
                <i class="bi bi-calendar-week me-2"></i>
                Calendario hospitalario
            </h3>
            <p class="text-muted mb-0">
                {{ $isSupervisor
                    ? 'Visualiza la distribución de tareas por horario, enfermera, prioridad y estado.'
                    : 'Consulta tus actividades del turno en una vista clara por horas.' }}
            </p>
        </div>

        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary fw-bold me-2">
                <i class="bi bi-card-checklist me-1"></i> Vista tarjetas
            </a>

            <a href="{{ route('tasks.pdf') }}" class="btn btn-outline-danger fw-bold">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF del turno
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Panel de filtros --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-lg-top calendar-filter-card">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-funnel-fill text-primary me-2"></i>
                        Filtros
                    </h5>
                    <small class="text-muted">Ajusta la vista del calendario.</small>
                </div>

                <div class="card-body">
                    @if($isSupervisor)
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Enfermera/o</label>
                            <select id="filterNurse" class="form-select">
                                <option value="">Todo el personal</option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->id }}">
                                        {{ $nurse->name }} — {{ $nurse->shift ?? 'Sin turno' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Estado</label>
                        <select id="filterStatus" class="form-select">
                            <option value="activas" selected>Activas</option>
                            <option value="todas">Todas</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="reprogramada">Reprogramada</option>
                            <option value="completada">Completada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Prioridad</label>
                        <select id="filterPriority" class="form-select">
                            <option value="">Todas</option>
                            <option value="critica">Crítica</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Categoría</label>
                        <select id="filterCategory" class="form-select">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary w-100 fw-bold" onclick="refreshCalendar()">
                        <i class="bi bi-search me-1"></i> Aplicar filtros
                    </button>

                    <button type="button" class="btn btn-light border w-100 fw-bold mt-2" onclick="clearCalendarFilters()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
                    </button>

                    <hr>

                    <div class="small text-muted">
                        <div class="fw-bold mb-3">Colores del calendario</div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#212529;"></span>
                            <span>Crítica</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#dc3545;"></span>
                            <span>Alta</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#fd7e14;"></span>
                            <span>Media</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#ffc107;"></span>
                            <span>Reprogramada</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#198754;"></span>
                            <span>Completada</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch" style="background:#6c757d;"></span>
                            <span>Cancelada</span>
                        </div>

                        <div class="legend-item">
                            <span class="legend-swatch legend-delayed"></span>
                            <span>Retrasada</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calendario --}}
        <div class="col-lg-9 mb-4">
            <div class="card border-0 shadow-sm calendar-card">
                <div class="card-body p-2 p-md-4">
                    <div id="hospitalCalendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FullCalendar --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.20/locales-all.global.min.js"></script>

<script>
    let hospitalCalendar = null;

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('hospitalCalendar');

        hospitalCalendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: window.innerWidth < 768 ? 'listDay' : 'timeGridWeek',
            nowIndicator: true,
            height: 'auto',
            expandRows: true,
            allDaySlot: true,
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            eventMinHeight: 52,
            eventShortHeight: 44,
            navLinks: true,
            dayMaxEvents: true,
            nowIndicatorClassNames: ['custom-now-indicator'],

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: window.innerWidth < 768
                    ? 'listDay,timeGridDay'
                    : 'timeGridWeek,timeGridDay,listWeek'
            },

            buttonText: {
                today: 'Hoy',
                week: 'Semana',
                day: 'Día',
                list: 'Lista'
            },

            events: {
                url: "{{ route('tasks.calendarEvents') }}",
                method: 'GET',
                extraParams: function () {
                    return getCalendarFilters();
                },
                failure: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las tareas del calendario.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },

            eventClick: function (info) {
                showTaskDetail(info.event);
            },

            eventDidMount: function(info) {
                const props = info.event.extendedProps;

                if (props.is_delayed) {
                    info.el.classList.add('event-delayed');
                    info.el.title = 'Tarea retrasada';
                }

                if (props.priority_key === 'critica') {
                    info.el.classList.add('event-critical');
                }
            },

            eventContent: function(arg) {
                const props = arg.event.extendedProps;

                const timeText = arg.timeText
                    ? `<div class="fc-task-time">${arg.timeText}</div>`
                    : '';

                const nurseLine = props.nurse
                    ? `<div class="fc-task-nurse">${props.nurse}</div>`
                    : '';

                const metaLine = `
                    <div class="fc-task-meta">
                        ${props.location || 'Sin ubicación'} · ${props.status || ''}
                    </div>
                `;

                return {
                    html: `
                        <div class="fc-task-content">
                            ${timeText}
                            <div class="fc-task-title">${arg.event.title}</div>
                            ${nurseLine}
                            ${metaLine}
                        </div>
                    `
                };
            }
        });

        hospitalCalendar.render();
    });

    function getCalendarFilters() {
        const filters = {};

        const nurse = document.getElementById('filterNurse');
        const status = document.getElementById('filterStatus');
        const priority = document.getElementById('filterPriority');
        const category = document.getElementById('filterCategory');

        if (nurse && nurse.value) {
            filters.nurse_id = nurse.value;
        }

        if (status && status.value) {
            filters.status = status.value;
        }

        if (priority && priority.value) {
            filters.priority = priority.value;
        }

        if (category && category.value) {
            filters.category_id = category.value;
        }

        return filters;
    }

    function refreshCalendar() {
        if (hospitalCalendar) {
            hospitalCalendar.refetchEvents();

            Swal.fire({
                icon: 'success',
                title: 'Filtros aplicados',
                timer: 900,
                showConfirmButton: false
            });
        }
    }

    function clearCalendarFilters() {
        const nurse = document.getElementById('filterNurse');
        const status = document.getElementById('filterStatus');
        const priority = document.getElementById('filterPriority');
        const category = document.getElementById('filterCategory');

        if (nurse) nurse.value = '';
        if (status) status.value = 'activas';
        if (priority) priority.value = '';
        if (category) category.value = '';

        if (hospitalCalendar) {
            hospitalCalendar.refetchEvents();
        }
    }

    function showTaskDetail(event) {
        const props = event.extendedProps;

        const delayedAlert = props.is_delayed
            ? `<div class="alert alert-danger py-2 mt-2">
                    <i class="bi bi-alarm-fill me-1"></i>
                    Esta tarea está retrasada según su fecha y hora programada.
               </div>`
            : '';

        const observations = props.observations
            ? `<p class="mb-0">
                    <strong>Observaciones:</strong><br>
                    ${props.observations}
               </p>`
            : '';

        Swal.fire({
            title: props.raw_title || event.title,
            html: `
                <div class="text-start">
                    <div class="mb-2">
                        <span class="badge bg-primary">${props.category}</span>
                        <span class="badge bg-dark">${props.priority}</span>
                        <span class="badge bg-secondary">${props.status}</span>
                    </div>

                    ${delayedAlert}

                    <p class="mb-1">
                        <strong>Enfermera/o:</strong> ${props.nurse}
                    </p>

                    <p class="mb-1">
                        <strong>Fecha:</strong> ${props.date}
                    </p>

                    <p class="mb-1">
                        <strong>Hora:</strong> ${props.time}
                    </p>

                    <p class="mb-1">
                        <strong>Ubicación:</strong> ${props.location}
                    </p>

                    <p class="mb-1">
                        <strong>Asignada por:</strong> ${props.supervisor}
                    </p>

                    <hr>

                    <p class="mb-1">
                        <strong>Descripción:</strong><br>
                        ${props.description}
                    </p>

                    ${observations}
                </div>
            `,
            icon: props.is_delayed ? 'warning' : 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#0d6efd',
            width: 650
        });
    }
</script>

<style>
    .calendar-filter-card {
        top: 90px;
        border-radius: 18px;
    }

    .calendar-card {
        border-radius: 22px;
        overflow: hidden;
    }

    #hospitalCalendar {
        min-height: 720px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: .92rem;
    }

    .legend-swatch {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        display: inline-block;
        border: 1px solid rgba(0,0,0,.12);
        flex-shrink: 0;
    }

    .legend-delayed {
        background: repeating-linear-gradient(
            45deg,
            #dc3545,
            #dc3545 4px,
            #ffffff 4px,
            #ffffff 8px
        );
        border: 1px solid #dc3545;
    }

    .fc {
        font-family: inherit;
    }

    .fc .fc-toolbar-title {
        font-weight: 800;
        color: #0f172a;
        text-transform: capitalize;
    }

    .fc .fc-button {
        border-radius: 10px !important;
        font-weight: 700 !important;
        box-shadow: none !important;
    }

    .fc .fc-button-primary {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #084298 !important;
        border-color: #084298 !important;
    }

    .fc-event {
        border-radius: 12px !important;
        padding: 0 !important;
        border-width: 0 !important;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
        cursor: pointer;
        overflow: hidden;
    }

    .fc .fc-timegrid-event {
        min-height: 58px;
    }

    .fc-task-content {
        line-height: 1.2;
        overflow: hidden;
        padding: 6px 8px;
    }

    .fc-task-time {
        font-size: .70rem;
        font-weight: 700;
        opacity: .95;
        margin-bottom: 2px;
    }

    .fc-task-title {
        font-weight: 800;
        font-size: .80rem;
        line-height: 1.15;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 2px;
    }

    .fc-task-nurse {
        font-size: .68rem;
        font-weight: 600;
        opacity: .95;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    .fc-task-meta {
        font-size: .66rem;
        opacity: .9;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .event-delayed {
        outline: 3px solid rgba(220, 53, 69, .35) !important;
    }

    .event-critical {
        animation: criticalPulse 1.6s infinite;
    }

    @keyframes criticalPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(33, 37, 41, .38);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(33, 37, 41, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(33, 37, 41, 0);
        }
    }

    @media (max-width: 767px) {
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #hospitalCalendar {
            min-height: 560px;
        }

        .fc .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.15rem;
            text-align: center;
        }

        .fc .fc-button {
            padding: .35rem .55rem !important;
            font-size: .8rem !important;
        }

        .calendar-filter-card {
            position: relative !important;
            top: 0;
        }

        .fc-task-meta {
            display: none;
        }

        .fc-task-nurse {
            display: none;
        }

        .fc-task-title {
            -webkit-line-clamp: 2;
            font-size: .78rem;
        }
    }
</style>
@endsection