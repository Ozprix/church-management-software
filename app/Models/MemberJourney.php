<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberJourney extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'stage',
        'stage_date',
        'previous_stage',
        'notes',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stage_date' => 'date',
    ];

    /**
     * Get the member that owns the journey record.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the user who updated this journey record.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all available journey stages.
     *
     * @return array
     */
    public static function getStages(): array
    {
        return [
            'visitor' => 'Visitor',
            'regular' => 'Regular Attendee',
            'committed' => 'Committed Member',
            'leader' => 'Leader',
        ];
    }
}
