<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_by',
        'category_id',
        'title',
        'location',
        'description',
        'due_date',
        'due_time',
        'priority',
        'status',
        'started_at',
        'completed_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}