<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the volunteers assigned to this role.
     */
    public function volunteers()
    {
        return $this->hasMany(Volunteer::class, 'role_id');
    }

    /**
     * Get the active volunteers assigned to this role.
     */
    public function activeVolunteers()
    {
        return $this->volunteers()->active();
    }

    /**
     * Get the count of active volunteers for this role.
     *
     * @return int
     */
    public function getActiveVolunteerCountAttribute()
    {
        return $this->activeVolunteers()->count();
    }

    /**
     * Get the members assigned to this volunteer role.
     */
    public function members()
    {
        return $this->hasManyThrough(
            Member::class,
            Volunteer::class,
            'role_id', // Foreign key on Volunteer table
            'id', // Foreign key on Member table
            'id', // Local key on VolunteerRole table
            'member_id' // Local key on Volunteer table
        );
    }
}