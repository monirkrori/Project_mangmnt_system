<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'disk',
        'file_name',
        'file_size',
        'mime_type',
        'attachable_id',
        'attachable_type'
    ];

    protected $appends = [
        'url',
        'formatted_file_size',
        'file_extension'
    ];

    /**
     * Get the parent attachable model
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get download URL (for API responses)
     */
    public function getUrlAttribute(): string
    {
        return $this->disk === 'public'
        ? Storage::disk('public')->url($this->path)
        : route('attachments.download', $this);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $unit = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $unit), 2) . ' ' . $units[$unit];
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Delete file when model is deleted
     */
    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }
}
