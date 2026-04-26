<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonnelController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/personal', [PersonnelController::class, 'index'])->name('personnel.index');
Route::post('/personal', [PersonnelController::class, 'store'])->name('personnel.store');
Route::put('/personal/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
Route::delete('/personal/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');