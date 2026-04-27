<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TaskController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $user = Auth::user();
        if ($user->role === 'supervisor') {
            $tasks = Task::with(['user', 'category'])->orderBy('due_date')->orderBy('due_time')->get();
            $nurses = User::where('role', 'enfermeria')->get();
            $categories = Category::all();
            return view('tasks.index', compact('tasks', 'nurses', 'categories'));
        } else {
            $pendingTasks = Task::with('category')->where('user_id', $user->id)->whereIn('status', ['pendiente', 'en_proceso'])->orderBy('priority', 'desc')->orderBy('due_time')->get();
            $completedTasks = Task::with('category')->where('user_id', $user->id)->where('status', 'completada')->orderBy('updated_at', 'desc')->get();
            $categories = Category::all(); 
            return view('tasks.index', compact('pendingTasks', 'completedTasks', 'categories'));
        }
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'priority' => 'required|in:alta,media,baja',
        ]);

        Task::create([
            'user_id' => $request->user_id,
            'assigned_by' => Auth::id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'location' => $request->location,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'priority' => $request->priority,
            'status' => 'pendiente',
        ]);

        return back()->with('success', 'Tarea guardada correctamente en la agenda.');
    }

    public function updateStatus(Request $request, Task $task) {
        $task->update(['status' => $request->status]);
        $mensaje = $request->status == 'completada' ? '¡Excelente! Tarea movida al historial.' : 'Estado actualizado.';
        return back()->with('success', $mensaje);
    }

    public function destroy(Task $task) {
        $task->delete();
        return back()->with('success', 'Tarea eliminada.');
    }

    public function downloadPdf() {
        $user = Auth::user();
        $date = date('Y-m-d'); // Fecha de hoy
        
        if ($user->role === 'supervisor') {
            // El Jefe imprime todas las tareas del día
            $tasks = Task::with(['user', 'category'])->whereDate('due_date', $date)->orderBy('due_time')->get();
            $title = "Hoja de Ruta General - Turno: " . date('d/m/Y');
        } else {
            // La enfermera imprime solo sus tareas del día
            $tasks = Task::with('category')->where('user_id', $user->id)->whereDate('due_date', $date)->orderBy('due_time')->get();
            $title = "Mi Hoja de Ruta Clínica - " . $user->name;
        }

        $pdf = Pdf::loadView('tasks.pdf', compact('tasks', 'title', 'user'));
        return $pdf->download('Reporte_Turno_' . date('d_m_Y') . '.pdf');
    }
}