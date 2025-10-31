<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChecklistPhoto extends Model
{
    protected $table = 'checklist_photos';
    protected $primaryKey = 'photo_id';
    
    const UPDATED_AT = null;
    const CREATED_AT = 'uploaded_at';

    protected $fillable = [
        'checklist_id',
        'photo_path',
        'photo_type',
        'original_filename',
        'file_size',
        'mime_type',
        'caption',
    ];

    protected $casts = [
        'photo_id' => 'integer',
        'checklist_id' => 'integer',
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the checklist this photo belongs to
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(DailyChecklist::class, 'checklist_id', 'checklist_id');
    }

    /**
     * Get the full URL to the photo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->photo_path);
    }

    /**
     * Get the absolute path to the photo
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->photo_path);
    }

    /**
     * Check if photo file exists
     */
    public function exists(): bool
    {
        return Storage::exists($this->photo_path);
    }

    /**
     * Delete photo file from storage
     */
    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return Storage::delete($this->photo_path);
        }
        
        return true;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}



