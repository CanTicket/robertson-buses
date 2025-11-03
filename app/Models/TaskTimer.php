<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimer extends Model
{
    protected $table = 'task_timers';
    
    protected $fillable = [
        'user_id',
        'company_id',
        'time_started',
        'time_finished',
        'notes',
    ];

    protected $casts = [
        'time_started' => 'datetime',
        'time_finished' => 'datetime',
    ];

    /**
     * Get the user this timer belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Check if timer is active (not finished)
     */
    public function isActive(): bool
    {
        return is_null($this->time_finished);
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutes(): ?int
    {
        if (!$this->time_finished) {
            return null;
        }

        return $this->time_started->diffInMinutes($this->time_finished);
    }
}
