<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'shift',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    // Tareas que tiene asignadas una enfermera.
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    // Tareas que fueron asignadas por un supervisor.
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Ayudas de rol
    |--------------------------------------------------------------------------
    */

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isNurse(): bool
    {
        return $this->role === 'enfermeria';
    }
}