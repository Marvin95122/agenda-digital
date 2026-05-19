<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Template;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | Panel principal
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            return $this->supervisorDashboard();
        }

        return $this->nurseDashboard($user);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard del Jefe de Piso / Supervisor
    |--------------------------------------------------------------------------
    */

    private function supervisorDashboard()
    {
        $today = today();

        $personalCount = User::where('role', 'enfermeria')->count();

        $tareasActivasCount = Task::whereIn('status', Task::ACTIVE_STATUSES)->count();

        $tareasCriticasCount = Task::whereIn('status', Task::ACTIVE_STATUSES)
            ->where('priority', 'critica')
            ->count();

        $tareasAltaPrioridadCount = Task::whereIn('status', Task::ACTIVE_STATUSES)
            ->whereIn('priority', ['critica', 'alta'])
            ->count();

        $tareasCompletadasHoyCount = Task::where('status', 'completada')
            ->whereDate('completed_at', $today)
            ->count();

        $protocolosCount = Template::count();

        /*
        |--------------------------------------------------------------------------
        | Balanceo de cargas
        |--------------------------------------------------------------------------
        | Calcula tareas activas, críticas, retrasadas y porcentaje de carga
        | por enfermera.
        */

        $workload = User::where('role', 'enfermeria')
            ->orderBy('name')
            ->get()
            ->map(function ($nurse) {
                $tasks = Task::where('user_id', $nurse->id)
                    ->whereIn('status', Task::ACTIVE_STATUSES)
                    ->get();

                $criticalTasks = $tasks->where('priority', 'critica')->count();
                $highTasks = $tasks->where('priority', 'alta')->count();

                $delayedTasks = $tasks->filter(function ($task) {
                    return $this->isTaskDelayed($task);
                })->count();

                $pendingTasks = $tasks->count();

                /*
                |--------------------------------------------------------------------------
                | Fórmula sencilla para carga:
                | - Cada tarea activa suma 1 punto.
                | - Cada tarea crítica suma peso extra.
                | - Cada tarea retrasada suma peso extra.
                |--------------------------------------------------------------------------
                */

                $workloadScore = $pendingTasks + ($criticalTasks * 1.5) + ($delayedTasks * 2);
                $workloadPercent = min(100, round(($workloadScore / 8) * 100));

                $nurse->pending_tasks = $pendingTasks;
                $nurse->critical_tasks = $criticalTasks;
                $nurse->high_tasks = $highTasks;
                $nurse->delayed_tasks = $delayedTasks;
                $nurse->workload_percent = $workloadPercent;

                if ($workloadPercent >= 80 || $criticalTasks >= 2 || $delayedTasks >= 2) {
                    $nurse->load_status = 'Carga alta';
                    $nurse->load_badge = 'bg-danger';
                    $nurse->progress_class = 'bg-danger';
                } elseif ($workloadPercent >= 45 || $criticalTasks >= 1) {
                    $nurse->load_status = 'Carga media';
                    $nurse->load_badge = 'bg-warning text-dark';
                    $nurse->progress_class = 'bg-warning';
                } else {
                    $nurse->load_status = 'Disponible';
                    $nurse->load_badge = 'bg-success';
                    $nurse->progress_class = 'bg-success';
                }

                return $nurse;
            })
            ->sortByDesc('workload_percent')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Enfermera recomendada
        |--------------------------------------------------------------------------
        | Se recomienda quien tenga menos retrasos, menos tareas críticas y
        | menor cantidad de tareas activas.
        */

        $recommendedNurse = $workload
            ->sortBy(function ($nurse) {
                return ($nurse->delayed_tasks * 10)
                    + ($nurse->critical_tasks * 6)
                    + ($nurse->pending_tasks * 2)
                    + $nurse->workload_percent;
            })
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Tareas urgentes del turno
        |--------------------------------------------------------------------------
        */

        $urgentTasks = Task::with(['user', 'category'])
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->whereIn('priority', ['critica', 'alta'])
            ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Conteo de tareas retrasadas globales
        |--------------------------------------------------------------------------
        */

        $tareasRetrasadasCount = Task::whereIn('status', Task::ACTIVE_STATUSES)
            ->get()
            ->filter(function ($task) {
                return $this->isTaskDelayed($task);
            })
            ->count();

        return view('home', compact(
            'personalCount',
            'tareasActivasCount',
            'tareasCriticasCount',
            'tareasAltaPrioridadCount',
            'tareasCompletadasHoyCount',
            'protocolosCount',
            'workload',
            'recommendedNurse',
            'urgentTasks',
            'tareasRetrasadasCount'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard de enfermería
    |--------------------------------------------------------------------------
    */

    private function nurseDashboard(User $user)
    {
        $today = today();

        $tareasHoy = Task::with(['category', 'assignedBy'])
            ->where('user_id', $user->id)
            ->whereDate('due_date', $today)
            ->orderBy('due_time')
            ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 WHEN 'baja' THEN 4 ELSE 5 END")
            ->get();

        $misTareasHoyCount = $tareasHoy
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->count();

        $misCompletadasHoyCount = $tareasHoy
            ->where('status', 'completada')
            ->count();

        $misUrgentesCount = Task::where('user_id', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->whereIn('priority', ['critica', 'alta'])
            ->count();

        $misRetrasadasCount = Task::where('user_id', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->get()
            ->filter(function ($task) {
                return $this->isTaskDelayed($task);
            })
            ->count();

        $proximaTarea = Task::with(['category', 'assignedBy'])
            ->where('user_id', $user->id)
            ->whereIn('status', Task::ACTIVE_STATUSES)
            ->whereDate('due_date', $today)
            ->whereNotNull('due_time')
            ->orderBy('due_time')
            ->first();

        $totalHoy = $tareasHoy->count();
        $progresoHoy = $totalHoy > 0
            ? round(($misCompletadasHoyCount / $totalHoy) * 100)
            : 0;

        return view('home', compact(
            'tareasHoy',
            'misTareasHoyCount',
            'misCompletadasHoyCount',
            'misUrgentesCount',
            'misRetrasadasCount',
            'proximaTarea',
            'progresoHoy'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar si una tarea está retrasada
    |--------------------------------------------------------------------------
    */

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

            $taskDateTime = Carbon::parse($date . ' ' . $time);

            return $taskDateTime->isPast();
        } catch (\Throwable $e) {
            return false;
        }
    }
}