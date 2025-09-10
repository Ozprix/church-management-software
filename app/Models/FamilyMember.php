<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'family_id',
        'member_id',
        'relationship',
    ];

    /**
     * Get the family that the relationship belongs to.
     */
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the member that the relationship belongs to.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}