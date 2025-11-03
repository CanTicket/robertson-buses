<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DailyChecklist extends Model
{
    protected $table = 'daily_checklists';
    protected $primaryKey = 'checklist_id';
    
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'checklist_uuid',
        'shift_timer_id',
        'vehicle_id',
        'user_id',
        'company_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'kids_left_alert',
        'alert_sent',
        'completed_at',
    ];

    protected $casts = [
        'checklist_id' => 'integer',
        'shift_timer_id' => 'integer',
        'vehicle_id' => 'integer',
        'company_id' => 'integer',
        'kids_left_alert' => 'boolean',
        'alert_sent' => 'boolean',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function to generate UUID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->checklist_uuid)) {
                $model->checklist_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the vehicle this checklist belongs to
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    /**
     * Get the user/driver who completed this checklist
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the manager who reviewed this checklist
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    /**
     * Get the shift/timer record
     */
    public function shiftTimer(): BelongsTo
    {
        return $this->belongsTo(TaskTimer::class, 'shift_timer_id', 'id');
    }

    /**
     * Get all checklist items
     */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_id', 'checklist_id')
            ->orderBy('sort_order');
    }

    /**
     * Get all photos for this checklist
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ChecklistPhoto::class, 'checklist_id', 'checklist_id');
    }

    /**
     * Get the company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * Check if checklist is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Completed' || $this->status === 'Approved';
    }

    /**
     * Check if checklist needs manager review
     */
    public function needsReview(): bool
    {
        return $this->status === 'Completed' && empty($this->reviewed_by);
    }

    /**
     * Mark as completed
     */
    public function markCompleted()
    {
        $this->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Approve checklist
     */
    public function approve($reviewerId, $notes = null)
    {
        $this->update([
            'status' => 'Approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Flag checklist for follow-up
     */
    public function flag($reviewerId, $notes)
    {
        $this->update([
            'status' => 'Flagged',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Scope: Pending checklists
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope: Completed but not reviewed
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'Completed')
            ->whereNull('reviewed_by');
    }

    /**
     * Scope: With kids left alert
     */
    public function scopeWithKidsAlert($query)
    {
        return $query->where('kids_left_alert', true);
    }

    /**
     * Scope: Filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}



