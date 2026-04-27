<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\Template;
use Carbon\Carbon; // IMPORTANTE PARA CALCULAR LOS RETRASOS

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'supervisor') {
            $personalCount = User::where('role', 'enfermeria')->count();
            $tareasPendientesCount = Task::where('status', 'pendiente')->count();
            $protocolosCount = Template::count();

            // NÚCLEO DEL BALANCEO DE CARGAS (Mágia de cálculo de tiempos)
            $workload = User::where('role', 'enfermeria')->get()->map(function($nurse) {
                $tasks = Task::where('user_id', $nurse->id)->whereIn('status', ['pendiente', 'en_proceso'])->get();
                
                // Buscar tareas retrasadas (La hora programada ya pasó)
                $delayed = $tasks->filter(function($task) {
                    if (!$task->due_time) return false;
                    $taskDateTime = Carbon::parse($task->due_date . ' ' . $task->due_time);
                    return $taskDateTime->isPast();
                })->count();

                $nurse->pending_tasks = $tasks->count();
                $nurse->delayed_tasks = $delayed;
                
                // Calculamos porcentaje (Suponemos que 8 tareas simultáneas es el 100% de carga)
                $capacity = ($nurse->pending_tasks / 8) * 100;
                $nurse->workload_percent = $capacity > 100 ? 100 : $capacity;

                return $nurse;
            })->sortByDesc('pending_tasks'); // Ordena mostrando a los más ocupados primero

            return view('home', compact('personalCount', 'tareasPendientesCount', 'protocolosCount', 'workload'));
        } else {
            $misTareasHoyCount = Task::where('user_id', $user->id)->whereIn('status', ['pendiente', 'en_proceso'])->whereDate('due_date', today())->count();
            $misCompletadasCount = Task::where('user_id', $user->id)->where('status', 'completada')->count();
            return view('home', compact('misTareasHoyCount', 'misCompletadasCount'));
        }
    }
}