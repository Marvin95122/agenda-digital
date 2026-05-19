<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | Vista principal de agenda
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            $tasksQuery = Task::with(['user', 'assignedBy', 'category']);

            $statusFilter = $request->input('status', 'activas');

            if ($statusFilter === 'activas') {
                $tasksQuery->whereIn('status', Task::ACTIVE_STATUSES);
            } elseif ($statusFilter !== 'todas' && $statusFilter !== null) {
                $tasksQuery->where('status', $statusFilter);
            }

            if ($request->filled('nurse_id')) {
                $tasksQuery->where('user_id', $request->nurse_id);
            }

            if ($request->filled('priority')) {
                $tasksQuery->where('priority', $request->priority);
            }

            if ($request->filled('category_id')) {
                $tasksQuery->where('category_id', $request->category_id);
            }

            if ($request->filled('date_from')) {
                $tasksQuery->whereDate('due_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $tasksQuery->whereDate('due_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);

                $tasksQuery->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('observations', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            }

            $tasks = $tasksQuery
                ->orderBy('due_date')
                ->orderBy('due_time')
                ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 WHEN 'baja' THEN 4 ELSE 5 END")
                ->paginate(9)
                ->withQueryString();

            $nurses = User::where('role', 'enfermeria')
                ->orderBy('name')
                ->get();

            $categories = Category::orderBy('name')->get();

            $summary = [
                'activas' => Task::whereIn('status', Task::ACTIVE_STATUSES)->count(),
                'criticas' => Task::whereIn('status', Task::ACTIVE_STATUSES)->where('priority', 'critica')->count(),
                'completadas_hoy' => Task::where('status', 'completada')->whereDate('completed_at', today())->count(),
                'reprogramadas' => Task::where('status', 'reprogramada')->count(),
                'canceladas' => Task::where('status', 'cancelada')->count(),
                'resultado_actual' => $tasks->total(),
            ];

            return view('tasks.index', compact('tasks', 'nurses', 'categories', 'summary'));
        }

        /*
        |--------------------------------------------------------------------------
        | Vista de enfermería
        |--------------------------------------------------------------------------
        */

        $pendingTasks = Task::with(['category', 'assignedBy'])
            ->where('user_id', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 WHEN 'baja' THEN 4 ELSE 5 END")
            ->get();

        $completedTasks = Task::with(['category', 'assignedBy'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completada', 'cancelada'])
            ->orderByDesc('completed_at')
            ->orderByDesc('cancelled_at')
            ->orderByDesc('updated_at')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('tasks.index', compact('pendingTasks', 'completedTasks', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | Vista calendario
    |--------------------------------------------------------------------------
    */

    public function calendar()
    {
        $user = Auth::user();

        $nurses = $user->isSupervisor()
            ? User::where('role', 'enfermeria')->orderBy('name')->get()
            : collect();

        $categories = Category::orderBy('name')->get();

        return view('tasks.calendar', compact('nurses', 'categories'));
    }

    /*
    |--------------------------------------------------------------------------
    | Eventos para FullCalendar
    |--------------------------------------------------------------------------
    */

    public function calendarEvents(Request $request)
    {
        $user = Auth::user();

        $query = Task::with(['user', 'assignedBy', 'category']);

        if (!$user->isSupervisor()) {
            $query->where('user_id', $user->id);
        }

        if ($user->isSupervisor() && $request->filled('nurse_id')) {
            $query->where('user_id', $request->nurse_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $statusFilter = $request->input('status', 'activas');

        if ($statusFilter === 'activas') {
            $query->whereIn('status', Task::ACTIVE_STATUSES);
        } elseif ($statusFilter !== 'todas' && $statusFilter !== null) {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        /*
        |--------------------------------------------------------------------------
        | FullCalendar envía start y end según el rango visible.
        |--------------------------------------------------------------------------
        */

        if ($request->filled('start')) {
            $query->whereDate('due_date', '>=', Carbon::parse($request->start)->toDateString());
        }

        if ($request->filled('end')) {
            $query->whereDate('due_date', '<=', Carbon::parse($request->end)->toDateString());
        }

        $tasks = $query->get();

        $events = $tasks->map(function (Task $task) use ($user) {
            $date = $task->due_date
                ? Carbon::parse($task->due_date)->format('Y-m-d')
                : now()->format('Y-m-d');

            $hasTime = !empty($task->due_time);

            if ($hasTime) {
                $time = substr((string) $task->due_time, 0, 5);
                $start = Carbon::parse($date . ' ' . $time);

                /*
                |--------------------------------------------------------------------------
                | Duración temporal:
                | Como aún no tenemos hora fin o duración real, se muestra un bloque de
                | 45 minutos.
                |--------------------------------------------------------------------------
                */
                $end = $start->copy()->addMinutes(45);
            } else {
                $start = Carbon::parse($date);
                $end = null;
            }

            $colors = $this->calendarColors($task);

            $title = $user->isSupervisor()
                ? ($task->title . ' · ' . ($task->user->name ?? 'Sin asignar'))
                : $task->title;

               // $title = $task->title;

            return [
                'id' => $task->id,
                'title' => $title,
                'start' => $hasTime ? $start->toIso8601String() : $start->toDateString(),
                'end' => $hasTime ? $end->toIso8601String() : null,
                'allDay' => !$hasTime,
                'backgroundColor' => $colors['background'],
                'borderColor' => $colors['border'],
                'textColor' => $colors['text'],
                'extendedProps' => [
                    'task_id' => $task->id,
                    'raw_title' => $task->title,
                    'nurse' => $task->user->name ?? 'Sin asignar',
                    'supervisor' => $task->assignedBy->name ?? 'Sin supervisor',
                    'category' => $task->category->name ?? 'General',
                    'location' => $task->location ?? 'Sin ubicación',
                    'priority' => $task->priority_label,
                    'priority_key' => $task->priority,
                    'status' => $task->status_label,
                    'status_key' => $task->status,
                    'description' => $task->description ?? 'Sin descripción',
                    'observations' => $task->observations ?? '',
                    'date' => $task->due_date ? Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha',
                    'time' => $task->due_time ? substr((string) $task->due_time, 0, 5) : 'Sin hora',
                    'is_delayed' => $this->isTaskDelayed($task),
                ],
            ];
        });

        return response()->json($events->values());
    }

    /*
    |--------------------------------------------------------------------------
    | Disponibilidad inteligente para asignación
    |--------------------------------------------------------------------------
    */

    public function availability(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSupervisor()) {
            abort(403, 'Solo el jefe de piso puede consultar disponibilidad.');
        }

        $validated = $request->validate([
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
        ]);

        $date = $validated['due_date'];
        $time = $validated['due_time'] ?? null;

        $nurses = User::where('role', 'enfermeria')
            ->orderBy('name')
            ->get()
            ->map(function ($nurse) use ($date, $time) {
                $activeTasks = Task::where('user_id', $nurse->id)
                    ->whereIn('status', Task::ACTIVE_STATUSES)
                    ->get();

                $pendingTasks = $activeTasks->count();

                $dayTasks = $activeTasks
                    ->filter(function ($task) use ($date) {
                        return $task->due_date &&
                            Carbon::parse($task->due_date)->format('Y-m-d') === $date;
                    })
                    ->count();

                $sameTimeTasks = 0;

                if ($time) {
                    $sameTimeTasks = $activeTasks
                        ->filter(function ($task) use ($date, $time) {
                            if (!$task->due_date || !$task->due_time) {
                                return false;
                            }

                            $taskDate = Carbon::parse($task->due_date)->format('Y-m-d');
                            $taskTime = substr((string) $task->due_time, 0, 5);

                            return $taskDate === $date && $taskTime === $time;
                        })
                        ->count();
                }

                $criticalTasks = $activeTasks->where('priority', 'critica')->count();
                $highTasks = $activeTasks->where('priority', 'alta')->count();

                $delayedTasks = $activeTasks
                    ->filter(function ($task) {
                        return $this->isTaskDelayed($task);
                    })
                    ->count();

                $score = ($sameTimeTasks * 12)
                    + ($delayedTasks * 8)
                    + ($criticalTasks * 7)
                    + ($highTasks * 4)
                    + ($dayTasks * 3)
                    + ($pendingTasks * 2);

                $workloadPercent = min(100, round(($score / 35) * 100));

                if ($sameTimeTasks > 0 || $workloadPercent >= 80 || $delayedTasks >= 2) {
                    $loadStatus = 'No recomendada';
                    $badgeClass = 'bg-danger';
                    $progressClass = 'bg-danger';
                } elseif ($workloadPercent >= 45 || $criticalTasks > 0 || $highTasks >= 2) {
                    $loadStatus = 'Carga media';
                    $badgeClass = 'bg-warning text-dark';
                    $progressClass = 'bg-warning';
                } else {
                    $loadStatus = 'Recomendada';
                    $badgeClass = 'bg-success';
                    $progressClass = 'bg-success';
                }

                return [
                    'id' => $nurse->id,
                    'name' => $nurse->name,
                    'shift' => $nurse->shift ?? 'Sin turno',
                    'pending_tasks' => $pendingTasks,
                    'day_tasks' => $dayTasks,
                    'same_time_tasks' => $sameTimeTasks,
                    'critical_tasks' => $criticalTasks,
                    'high_tasks' => $highTasks,
                    'delayed_tasks' => $delayedTasks,
                    'score' => $score,
                    'workload_percent' => $workloadPercent,
                    'load_status' => $loadStatus,
                    'badge_class' => $badgeClass,
                    'progress_class' => $progressClass,
                    'message' => $this->buildAvailabilityMessage($sameTimeTasks, $delayedTasks, $criticalTasks, $pendingTasks),
                ];
            })
            ->sortBy('score')
            ->values();

        return response()->json([
            'recommended' => $nurses->first(),
            'nurses' => $nurses,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear tarea
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
        ];

        if ($user->isSupervisor()) {
            $rules['user_id'] = [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('role', 'enfermeria');
                }),
            ];
        }

        $validated = $request->validate($rules);

        $assignedUserId = $user->isSupervisor()
            ? $validated['user_id']
            : $user->id;

        Task::create([
            'user_id' => $assignedUserId,
            'assigned_by' => $user->isSupervisor() ? $user->id : null,
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'observations' => $validated['observations'] ?? null,
            'due_date' => $validated['due_date'],
            'due_time' => $validated['due_time'] ?? null,
            'priority' => $validated['priority'],
            'status' => 'pendiente',
            'started_at' => null,
            'completed_at' => null,
            'rescheduled_at' => null,
            'cancelled_at' => null,
        ]);

        return back()->with('success', 'Tarea guardada correctamente en la agenda.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cambiar estado de tarea
    |--------------------------------------------------------------------------
    */

    public function updateStatus(Request $request, Task $task)
    {
        $this->ensureCanAccessTask($task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Task::STATUSES)],
            'status_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = $validated['status'];
        $statusNote = trim($validated['status_note'] ?? '');

        $data = [
            'status' => $status,
        ];

        if ($status === 'pendiente') {
            $data['completed_at'] = null;
            $data['cancelled_at'] = null;
            $data['cancel_reason'] = null;
        }

        if ($status === 'en_proceso') {
            $data['started_at'] = $task->started_at ?? now();
            $data['completed_at'] = null;
            $data['cancelled_at'] = null;
            $data['cancel_reason'] = null;
        }

        if ($status === 'completada') {
            $data['started_at'] = $task->started_at ?? now();
            $data['completed_at'] = now();
            $data['cancelled_at'] = null;
            $data['cancel_reason'] = null;
        }

        if ($status === 'reprogramada') {
            $data['rescheduled_at'] = now();
            $data['reschedule_reason'] = $statusNote ?: 'Reprogramación sin motivo especificado.';
            $data['completed_at'] = null;
            $data['cancelled_at'] = null;
            $data['cancel_reason'] = null;
        }

        if ($status === 'cancelada') {
            $data['cancelled_at'] = now();
            $data['cancel_reason'] = $statusNote ?: 'Cancelación sin motivo especificado.';
            $data['completed_at'] = null;
        }

        if ($statusNote && !in_array($status, ['reprogramada', 'cancelada'])) {
            $data['observations'] = $this->appendObservation($task->observations, $statusNote);
        }

        $task->update($data);

        $mensaje = match ($status) {
            'completada' => '¡Excelente! La tarea fue marcada como completada.',
            'en_proceso' => 'La tarea ahora está en proceso.',
            'reprogramada' => 'La tarea fue marcada como reprogramada.',
            'cancelada' => 'La tarea fue cancelada correctamente.',
            default => 'La tarea volvió a estado pendiente.',
        };

        return back()->with('success', $mensaje);
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar tarea
    |--------------------------------------------------------------------------
    */

    public function destroy(Task $task)
    {
        $this->ensureCanDeleteTask($task);

        $task->delete();

        return back()->with('success', 'Tarea eliminada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Descargar PDF
    |--------------------------------------------------------------------------
    */

    public function downloadPdf()
    {
        $user = Auth::user();
        $date = now()->toDateString();

        if ($user->isSupervisor()) {
            $tasks = Task::with(['user', 'assignedBy', 'category'])
                ->whereDate('due_date', $date)
                ->orderBy('due_time')
                ->get();

            $title = 'Hoja de Ruta General - Turno: ' . now()->format('d/m/Y');
        } else {
            $tasks = Task::with(['category', 'assignedBy'])
                ->where('user_id', $user->id)
                ->whereDate('due_date', $date)
                ->orderBy('due_time')
                ->get();

            $title = 'Mi Hoja de Ruta Clínica - ' . $user->name;
        }

        $pdf = Pdf::loadView('tasks.pdf', compact('tasks', 'title', 'user'));

        return $pdf->download('Reporte_Turno_' . now()->format('d_m_Y') . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Ayudas internas
    |--------------------------------------------------------------------------
    */

    private function ensureCanAccessTask(Task $task): void
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            return;
        }

        if ($task->user_id === $user->id) {
            return;
        }

        abort(403, 'No tienes permiso para modificar esta tarea.');
    }

    private function ensureCanDeleteTask(Task $task): void
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            return;
        }

        if ($task->user_id === $user->id && $task->assigned_by === null) {
            return;
        }

        abort(403, 'No tienes permiso para eliminar esta tarea.');
    }

    private function appendObservation(?string $currentObservations, string $newObservation): string
    {
        $line = now()->format('d/m/Y H:i') . ' - ' . $newObservation;

        if (!$currentObservations) {
            return $line;
        }

        return $currentObservations . PHP_EOL . $line;
    }

    private function isTaskDelayed(Task $task): bool
    {
        if (!$task->due_date || !$task->due_time) {
            return false;
        }

        if (in_array($task->status, ['completada', 'cancelada'])) {
            return false;
        }

        try {
            $date = Carbon::parse($task->due_date)->format('Y-m-d');
            $time = substr((string) $task->due_time, 0, 5);

            return Carbon::parse($date . ' ' . $time)->isPast();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function buildAvailabilityMessage(int $sameTimeTasks, int $delayedTasks, int $criticalTasks, int $pendingTasks): string
    {
        if ($sameTimeTasks > 0) {
            return 'Tiene tarea programada en el mismo horario.';
        }

        if ($delayedTasks > 0) {
            return 'Tiene tareas retrasadas que requieren atención.';
        }

        if ($criticalTasks > 0) {
            return 'Tiene tareas críticas activas.';
        }

        if ($pendingTasks === 0) {
            return 'Disponible para nueva asignación.';
        }

        return 'Carga manejable para nueva asignación.';
    }

    private function calendarColors(Task $task): array
    {
        if ($task->status === 'completada') {
            return [
                'background' => '#198754',
                'border' => '#198754',
                'text' => '#ffffff',
            ];
        }

        if ($task->status === 'reprogramada') {
            return [
                'background' => '#ffc107',
                'border' => '#ffc107',
                'text' => '#212529',
            ];
        }

        if ($task->status === 'cancelada') {
            return [
                'background' => '#6c757d',
                'border' => '#6c757d',
                'text' => '#ffffff',
            ];
        }

        return match ($task->priority) {
            'critica' => [
                'background' => '#212529',
                'border' => '#212529',
                'text' => '#ffffff',
            ],
            'alta' => [
                'background' => '#dc3545',
                'border' => '#dc3545',
                'text' => '#ffffff',
            ],
            'media' => [
                'background' => '#fd7e14',
                'border' => '#fd7e14',
                'text' => '#ffffff',
            ],
            'baja' => [
                'background' => '#0d6efd',
                'border' => '#0d6efd',
                'text' => '#ffffff',
            ],
            default => [
                'background' => '#6c757d',
                'border' => '#6c757d',
                'text' => '#ffffff',
            ],
        };
    }
}