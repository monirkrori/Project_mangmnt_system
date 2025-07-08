<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
protected $fillable = [
    'name',
    'description',
    'status',
    'due_date',
    'team_id',
    'created_by_user_id',
];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    // --- Scopes ---

    /**
     * SCOPE: Scope a query to only include active projects.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    // --- Relationships ---

    public function team() {
        return $this->belongsTo(Team::class);
    }
    public function creator() {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function members() {
        return $this->belongsToMany(User::class, 'project_user');
    }
    public function tasks() {
        return $this->hasMany(Task::class);
    }

    /**
     * ADVANCED RELATIONSHIP: hasManyThrough
     * Get all comments on all tasks of this project.
     * Accesses `Comment` through the intermediate `Task` model.
     */
    public function taskComments()
    {
        return $this->hasManyThrough(Comment::class, Task::class);
    }

    /**
     * POLYMORPHIC RELATIONSHIP: morphMany
     * Get all of the project's comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * POLYMORPHIC RELATIONSHIP: morphMany
     * Get all of the project's attachments.
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

}
