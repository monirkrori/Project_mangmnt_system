<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
  protected $fillable = [
    'name',
    'owner_id',
];

     public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The members of the team.
     * RELATIONSHIP: belongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    /**
     * The projects belonging to the team.
     * RELATIONSHIP: hasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

}
