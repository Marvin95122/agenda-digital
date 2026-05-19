<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public const PRIORITIES = ['critica', 'alta', 'media', 'baja'];

    public const STATUSES = [
        'pendiente',
        'en_proceso',
        'completada',
        'reprogramada',
        'cancelada',
    ];

    public const ACTIVE_STATUSES = [
        'pendiente',
        'en_proceso',
        'reprogramada',
    ];

    protected $fillable = [
        'user_id',
        'assigned_by',
        'category_id',
        'title',
        'location',
        'description',
        'observations',
        'due_date',
        'due_time',
        'priority',
        'status',
        'started_at',
        'completed_at',
        'reschedule_reason',
        'rescheduled_at',
        'cancel_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rescheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Etiquetas para mostrar en vistas
    |--------------------------------------------------------------------------
    */

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'critica' => 'Crítica',
            'alta' => 'Alta',
            'media' => 'Media',
            'baja' => 'Baja',
            default => 'Sin prioridad',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'completada' => 'Completada',
            'reprogramada' => 'Reprogramada',
            'cancelada' => 'Cancelada',
            default => 'Sin estado',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'critica' => 'bg-dark',
            'alta' => 'bg-danger',
            'media' => 'bg-warning text-dark',
            'baja' => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'bg-secondary',
            'en_proceso' => 'bg-primary',
            'completada' => 'bg-success',
            'reprogramada' => 'bg-warning text-dark',
            'cancelada' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getCardBorderClassAttribute(): string
    {
        return match ($this->priority) {
            'critica' => '#212529',
            'alta' => '#dc3545',
            'media' => '#ffc107',
            'baja' => '#0dcaf0',
            default => '#adb5bd',
        };
    }
}