<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //  public $incrementing = false;
    protected $fillable = [
    'user_id',
    'type',
    'data',
    'read_at',
];

    /**
     * The type of the primary key.
     * @var string
     */
    protected $keyType = 'string';

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // --- Scopes ---

    /**
     * SCOPE: Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // --- Relationships ---

    /**
     * The user that the notification belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
