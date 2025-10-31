<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $table = 'checklist_items';
    protected $primaryKey = 'item_id';
    
    const UPDATED_AT = null;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'checklist_id',
        'check_type',
        'check_label',
        'value',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'checklist_id' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the checklist this item belongs to
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(DailyChecklist::class, 'checklist_id', 'checklist_id');
    }

    /**
     * Check if this item indicates a problem
     */
    public function hasProblem(): bool
    {
        $value = strtolower($this->value);
        
        // Check for negative indicators
        $problemValues = ['poor', 'bad', 'fail', 'failed', 'yes', 'damaged'];
        
        foreach ($problemValues as $problem) {
            if (str_contains($value, $problem)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute(): string
    {
        return ucfirst($this->value);
    }
}



