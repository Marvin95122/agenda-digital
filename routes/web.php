<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rutas para la Gestión de Personal
Route::get('/personal', [PersonnelController::class, 'index'])->name('personnel.index');
Route::post('/personal', [PersonnelController::class, 'store'])->name('personnel.store');
Route::put('/personal/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
Route::delete('/personal/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');


// Rutas para la Gestión de Categorías
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');


// Rutas para la Agenda de Tareas
Route::get('/agenda', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/agenda', [TaskController::class, 'store'])->name('tasks.store');
Route::patch('/agenda/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
Route::delete('/agenda/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');


// Rutas para Protocolos (Plantillas)
Route::get('/protocolos', [TemplateController::class, 'index'])->name('templates.index');
Route::post('/protocolos', [TemplateController::class, 'store'])->name('templates.store');
Route::post('/protocolos/{template}/aplicar', [TemplateController::class, 'apply'])->name('templates.apply');
Route::delete('/protocolos/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');