<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'commentable_id',
        'commentable_type'
    ];

    protected $appends = [
        'formatted_created_at',
        'is_edited'
    ];

    /**
     * Get the parent commentable model
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * POLYMORPHIC RELATIONSHIP: xcomment can have many attachments
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get human-readable created_at timestamp
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if comment has been edited
     */
    public function getIsEditedAttribute(): bool
    {
        return $this->created_at->ne($this->updated_at);
    }

    /**
     * Scope for comments created by specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent comments first
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if comment can be edited by user
     */
    public function canBeEditedBy(User $user): bool
    {
        return $user->id === $this->user_id || $user->can('edit_all_comments');
    }
}
