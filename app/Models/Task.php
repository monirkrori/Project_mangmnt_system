<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * @property \App\Models\User $assignedUser
 */
class Task extends Model
{
    use HasFactory;
    protected $appends = ['is_overdue'];

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'due_date',
        'project_id',
        'assigned_to',
        'is_overdue'
    ];


    protected $casts = [
        'due_date' => 'datetime:Y-m-d',
    ];

    // --------------------------------------
    // Accessors & Mutators using Attribute class
    // --------------------------------------

    /**
     *  Ensure 'priority' is lowercase in DB but readable as Capitalized
     */
    protected function priority(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => strtolower($value)
        );
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) return false;

        return Carbon::parse($this->due_date)->isPast() && $this->status !== 'completed';
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     *  Format status text into a human-readable form
     *
     */
    protected function statusText(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst(str_replace('_', ' ', $this->status))
        );
    }

    /**
     * ACCESSOR: Return due date as formatted string (Y-m-d).
     */
    public function getFormattedDueDateAttribute(): ?string
    {
        return $this->due_date?->format('Y-m-d');
    }

    // --------------------------------------
    // Scopes
    // --------------------------------------

    /**
     *  Tasks that are not completed and past due date
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString());
    }

    /**
     *  Tasks marked as completed
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     *  Tasks with high priorit
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }


    // --------------------------------------
    // Relationships
    // --------------------------------------

    /**
     *  Task belongs to a project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     *  Task is assigned to a user
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     *  Task is created by a user
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * POLYMORPHIC RELATIONSHIP: Task can have many comments
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * POLYMORPHIC RELATIONSHIP: Task can have many attachments
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     *  Get the latest attachment for this task
     */
    public function latestAttachment()
    {
        return $this->hasOne(Attachment::class, 'attachable_id')
            ->ofMany(['created_at' => 'max'], fn ($q) =>
            $q->where('attachable_type', self::class)
            );
    }

    // --------------------------------------
    // Utility Methods
    // --------------------------------------

    /**
     * Check if the task was recently created (within current request lifecycle).
     */
    public function wasJustCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }

    /**
     * Check if the status was changed
     */
    public function isStatusChanged(): bool
    {
        return $this->isDirty('status');
    }

    /**
     *set up the created_by id with the auth user id
     */
    protected static function booted()
    {
        static::creating(function ($task) {
            if (auth()->check()) {
                $task->created_by = auth()->id();
            }
        });
    }


}
