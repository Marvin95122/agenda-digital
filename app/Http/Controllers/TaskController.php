<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

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
            $pendingTasks = Task::with('category')
                ->where('user_id', $user->id)
                ->whereIn('status', ['pendiente', 'en_proceso'])
                ->orderBy('priority', 'desc')
                ->orderBy('due_time')
                ->get();

            $completedTasks = Task::with('category')
                ->where('user_id', $user->id)
                ->where('status', 'completada')
                ->orderBy('updated_at', 'desc')
                ->get();

            // Pasamos las categorías para que la enfermera coloree sus tareas
            $categories = Category::all(); 

            return view('tasks.index', compact('pendingTasks', 'completedTasks', 'categories'));
        }
    }

    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:100',
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'priority' => 'required|in:alta,media,baja',
        ]);

        Task::create([
            'user_id' => $request->user_id,
            'assigned_by' => Auth::id(), // Si es enfermera, se asigna a sí misma
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
}