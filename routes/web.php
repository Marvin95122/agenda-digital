<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
| Se desactiva el registro público para que el personal sea creado
| desde el módulo de supervisión.
*/

Auth::routes([
    'register' => false,
]);

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
| Todo lo que está dentro de este grupo requiere iniciar sesión.
*/

Route::middleware('auth')->group(function () {

    // Panel principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    /*
    |--------------------------------------------------------------------------
    | Gestión de personal
    |--------------------------------------------------------------------------
    | Módulo pensado para el supervisor / jefe de piso.
    */
    Route::get('/personal', [PersonnelController::class, 'index'])->name('personnel.index');
    Route::post('/personal', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::put('/personal/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
    Route::delete('/personal/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

    /*
    |--------------------------------------------------------------------------
    | Gestión de categorías
    |--------------------------------------------------------------------------
    | Categorías como: Medicación, Signos vitales, Curaciones, Higiene, etc.
    */
    Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    /*
    |--------------------------------------------------------------------------
    | Agenda de tareas
    |--------------------------------------------------------------------------
    | Supervisor: ve y asigna tareas.
    | Enfermería: ve sus tareas y actualiza su estado.
    */
    Route::get('/agenda', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/agenda/calendario', [TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::get('/agenda/eventos', [TaskController::class, 'calendarEvents'])->name('tasks.calendarEvents');
    Route::get('/agenda/disponibilidad', [TaskController::class, 'availability'])->name('tasks.availability');
    Route::post('/agenda', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/agenda/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::delete('/agenda/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/agenda/pdf/descargar', [TaskController::class, 'downloadPdf'])->name('tasks.pdf');

    /*
    |--------------------------------------------------------------------------
    | Protocolos / plantillas clínicas
    |--------------------------------------------------------------------------
    | Permite crear plantillas de tareas y aplicarlas automáticamente.
    */
    Route::get('/protocolos', [TemplateController::class, 'index'])->name('templates.index');
    Route::post('/protocolos', [TemplateController::class, 'store'])->name('templates.store');
    Route::post('/protocolos/{template}/aplicar', [TemplateController::class, 'apply'])->name('templates.apply');
    Route::delete('/protocolos/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
});