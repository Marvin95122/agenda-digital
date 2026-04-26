<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\Template;

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
            // Cálculos para el Jefe de Piso
            $personalCount = User::where('role', 'enfermeria')->count();
            $tareasPendientesCount = Task::where('status', 'pendiente')->count();
            $protocolosCount = Template::count();

            return view('home', compact('personalCount', 'tareasPendientesCount', 'protocolosCount'));
        } else {
            // Cálculos para el Personal de Enfermería
            $misTareasHoyCount = Task::where('user_id', $user->id)
                                     ->where('status', 'pendiente')
                                     ->whereDate('due_date', today())
                                     ->count();
                                     
            $misCompletadasCount = Task::where('user_id', $user->id)
                                       ->where('status', 'completada')
                                       ->count();

            return view('home', compact('misTareasHoyCount', 'misCompletadasCount'));
        }
    }
}