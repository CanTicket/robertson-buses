<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    public $timestamps = false;

    protected $fillable = [
        'bus_number',
        'registration_number',
        'make',
        'model',
        'year',
        'capacity',
        'status',
        'company_id',
        'notes',
        'date_added',
        'date_updated',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'year' => 'integer',
        'capacity' => 'integer',
        'company_id' => 'integer',
        'date_added' => 'datetime',
        'date_updated' => 'datetime',
    ];

    /**
     * Get all checklists for this vehicle
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(DailyChecklist::class, 'vehicle_id', 'vehicle_id');
    }

    /**
     * Get the company this vehicle belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * Get recent checklists (last 30 days)
     */
    public function recentChecklists()
    {
        return $this->checklists()
            ->where('completed_at', '>=', now()->subDays(30))
            ->orderBy('completed_at', 'desc');
    }

    /**
     * Check if vehicle is available for assignment
     */
    public function isAvailable(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Get display name for vehicle
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->bus_number} ({$this->registration_number})";
    }

    /**
     * Scope: Active vehicles only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}



