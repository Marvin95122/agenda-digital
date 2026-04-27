<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\User;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        if (Auth::user()->role !== 'supervisor') abort(403);
        
        $templates = Template::all();
        $nurses = User::where('role', 'enfermeria')->get();
        $categories = Category::all();
        
        return view('templates.index', compact('templates', 'nurses', 'categories'));
    }

    public function store(Request $request) {
        if (Auth::user()->role !== 'supervisor') abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        // Recolectamos las tareas del formulario (ignoramos las vacías)
        $tasks = [];
        foreach($request->task_titles as $index => $title) {
            if(!empty($title)) {
                $tasks[] = [
                    'title' => $title,
                    'category_id' => $request->category_ids[$index] ?? null,
                    'priority' => $request->priorities[$index] ?? 'media',
                ];
            }
        }

        if(count($tasks) === 0) {
            return back()->withErrors(['Debe agregar al menos una tarea a la plantilla.']);
        }

        Template::create([
            'created_by' => Auth::id(),
            'name' => $request->name,
            'tasks_json' => $tasks
        ]);

        return back()->with('success', 'Protocolo médico guardado correctamente.');
    }

    public function apply(Request $request, Template $template) {
        if (Auth::user()->role !== 'supervisor') abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'due_date' => 'required|date'
        ]);

        // Magia: Creamos una tarea real por cada elemento en el JSON de la plantilla
        $tasksToCreate = $template->tasks_json;
        $count = 0;

        foreach($tasksToCreate as $taskData) {
            Task::create([
                'user_id' => $request->user_id,
                'assigned_by' => Auth::id(),
                'category_id' => $taskData['category_id'],
                'title' => $taskData['title'],
                'location' => $request->location,
                'due_date' => $request->due_date,
                'due_time' => $request->due_time,
                'priority' => $taskData['priority'],
                'status' => 'pendiente'
            ]);
            $count++;
        }

        return back()->with('success', "¡Excelente! Se asignaron {$count} tareas automáticamente.");
    }

    public function destroy(Template $template) {
        if (Auth::user()->role !== 'supervisor') abort(403);
        $template->delete();
        return back()->with('success', 'Plantilla eliminada.');
    }
}