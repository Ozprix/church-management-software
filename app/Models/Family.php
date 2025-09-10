<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'head_member_id',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'communication_preference',
    ];

    /**
     * Get the head member of the family.
     */
    public function headMember()
    {
        return $this->belongsTo(Member::class, 'head_member_id');
    }

    /**
     * Get all members of the family.
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Get all family relationships.
     */
    public function familyRelationships()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get all members with their relationships.
     */
    public function membersWithRelationships()
    {
        return $this->hasManyThrough(
            Member::class,
            FamilyMember::class,
            'family_id', // Foreign key on FamilyMember table
            'id', // Foreign key on Member table
            'id', // Local key on Family table
            'member_id' // Local key on FamilyMember table
        );
    }

    /**
     * Get the full address of the family.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip,
            $this->country
        ]);
        
        return implode(', ', $parts);
    }
}