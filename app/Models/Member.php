<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'membership_status_id',
        'first_name',
        'middle_name',
        'last_name',
        'maiden_name',
        'gender',
        'date_of_birth',
        'marital_status',
        'occupation',
        'employer',
        'phone',
        'work_phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'membership_date',
        'membership_end_date',
        'membership_type',
        'baptism_status',
        'baptism_date',
        'baptism_location',
        'notes',
        'profile_photo',
        'custom_fields',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'anniversary_date' => 'date',
        'membership_date' => 'date',
        'membership_end_date' => 'date',
        'baptism_date' => 'date',
        'custom_fields' => 'json',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name', 'age', 'is_active'];

    /**
     * Get the user associated with the member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the membership status of the member.
     */
    public function membershipStatus()
    {
        return $this->belongsTo(MemberStatus::class, 'membership_status_id');
    }


    /**
     * Get the member's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Get the member's age.
     *
     * @return int|null
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    /**
     * Check if the member is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        return $this->membershipStatus && $this->membershipStatus->is_active && 
               (!$this->membership_end_date || $this->membership_end_date->isFuture());
    }

    /**
     * Scope a query to only include active members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereHas('membershipStatus', function($q) {
            $q->where('is_active', true);
        })->where(function($q) {
            $q->whereNull('membership_end_date')
              ->orWhere('membership_end_date', '>=', now());
        });
    }

    /**
     * Scope a query to only include members with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->whereHas('membershipStatus', function($q) use ($status) {
            $q->where('name', $status);
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the family that the member belongs to.
     */
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the family relationships for the member.
     */
    public function familyRelationships()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Get the attendance records for the member.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the donations made by the member.
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get the prayer requests submitted by the member.
     */
    public function prayerRequests()
    {
        return $this->hasMany(PrayerRequest::class);
    }
    
    /**
     * Get the group prayer requests submitted by the member.
     */
    public function groupPrayerRequests()
    {
        return $this->hasMany(GroupPrayerRequest::class);
    }
    
    /**
     * Get the group prayer responses by the member.
     */
    public function groupPrayerResponses()
    {
        return $this->hasMany(GroupPrayerResponse::class);
    }

    /**
     * Get the volunteer roles of the member.
     */
    public function volunteerRoles()
    {
        return $this->hasMany(Volunteer::class);
    }
    
    /**
     * Get the groups that the member belongs to.
     * 
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'join_date', 'exit_date', 'notes', 'is_active')
            ->withTimestamps();
    }
    
    /**
     * Get only active group memberships.
     * 
     * @return BelongsToMany
     */
    public function activeGroups()
    {
        return $this->groups()
            ->wherePivot('is_active', true)
            ->wherePivot('exit_date', null);
    }
    
    /**
     * Get the journey history of the member.
     */
    public function journeys()
    {
        return $this->hasMany(MemberJourney::class)->orderBy('stage_date', 'desc');
    }
    
    /**
     * Get the group messages sent by the member.
     */
    public function sentGroupMessages()
    {
        return $this->hasMany(GroupMessage::class, 'sender_id');
    }
    
    /**
     * Get the group message recipients for this member.
     */
    public function groupMessageRecipients()
    {
        return $this->hasMany(GroupMessageRecipient::class);
    }
    
    /**
     * Get the group documents uploaded by this member.
     */
    public function uploadedGroupDocuments()
    {
        return $this->hasMany(GroupDocument::class, 'uploaded_by');
    }
    
    /**
     * Get the group document access records for this member.
     */
    public function groupDocumentAccesses()
    {
        return $this->hasMany(GroupDocumentAccess::class);
    }
    
    /**
     * Get the skills of the member.
     *
     * @return BelongsToMany
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'member_skills')
            ->withPivot('proficiency_level', 'notes', 'is_verified')
            ->withTimestamps();
    }
    
    /**
     * Get the interests of the member.
     *
     * @return BelongsToMany
     */
    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'member_interests')
            ->withPivot('interest_level', 'notes')
            ->withTimestamps();
    }
    
    /**
     * Get the spiritual gifts of the member.
     *
     * @return BelongsToMany
     */
    public function spiritualGifts()
    {
        return $this->belongsToMany(SpiritualGift::class, 'member_spiritual_gifts')
            ->withPivot('strength_level', 'notes', 'is_assessed', 'assessment_date')
            ->withTimestamps();
    }
    
    /**
     * Get the availability records for the member.
     *
     * @return HasMany
     */
    public function availability()
    {
        return $this->hasMany(MemberAvailability::class);
    }
    
    /**
     * Get the current availability records for the member.
     *
     * @return HasMany
     */
    public function currentAvailability()
    {
        return $this->hasMany(MemberAvailability::class)
            ->where(function($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            })
            ->where(function($query) {
                $query->whereNull('effective_date')
                    ->orWhere('effective_date', '<=', now());
            });
    }
    
    /**
     * Get the current journey stage of the member.
     */
    public function currentJourney()
    {
        return $this->hasOne(MemberJourney::class)->latestOfMany('stage_date');
    }



    /**
     * Scope a query to only include inactive members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('membership_status', 'inactive');
    }

    /**
     * Scope a query to only include pending members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('membership_status', 'pending');
    }

    /**
     * Check if the member is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->membership_status === 'active';
    }

    
    /**
     * Get the group attendance records for this member.
     */
    public function groupAttendances()
    {
        return $this->belongsToMany(GroupAttendance::class, 'group_attendance_details')
            ->withPivot('attendance_status', 'notes', 'is_first_time')
            ->withTimestamps();
    }
    
    /**
     * Get the group attendance details for this member.
     */
    public function groupAttendanceDetails()
    {
        return $this->hasMany(GroupAttendanceDetail::class);
    }
    
    /**
     * Get the attendance statistics for this member across all groups.
     * 
     * @param string $period
     * @return array
     */
    public function getGroupAttendanceStats($period = '3months')
    {
        $query = $this->groupAttendanceDetails();
        
        // Filter by period
        if ($period === '1month') {
            $query->whereHas('groupAttendance', function($q) {
                $q->where('attendance_date', '>=', now()->subMonth());
            });
        } elseif ($period === '3months') {
            $query->whereHas('groupAttendance', function($q) {
                $q->where('attendance_date', '>=', now()->subMonths(3));
            });
        } elseif ($period === '6months') {
            $query->whereHas('groupAttendance', function($q) {
                $q->where('attendance_date', '>=', now()->subMonths(6));
            });
        } elseif ($period === '1year') {
            $query->whereHas('groupAttendance', function($q) {
                $q->where('attendance_date', '>=', now()->subYear());
            });
        }
        
        $attendanceDetails = $query->with('groupAttendance')->get();
        
        if ($attendanceDetails->isEmpty()) {
            return [
                'total_meetings' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'excused_count' => 0,
                'attendance_rate' => 0,
                'groups_attended' => []
            ];
        }
        
        $presentCount = $attendanceDetails->where('attendance_status', 'present')->count();
        $absentCount = $attendanceDetails->where('attendance_status', 'absent')->count();
        $excusedCount = $attendanceDetails->where('attendance_status', 'excused')->count();
        $totalCount = $presentCount + $absentCount + $excusedCount;
        
        // Group attendance by group
        $groupsAttended = [];
        foreach ($attendanceDetails as $detail) {
            $groupId = $detail->groupAttendance->group_id;
            if (!isset($groupsAttended[$groupId])) {
                $groupsAttended[$groupId] = [
                    'group_id' => $groupId,
                    'total' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'excused' => 0
                ];
            }
            
            $groupsAttended[$groupId]['total']++;
            if ($detail->attendance_status === 'present') {
                $groupsAttended[$groupId]['present']++;
            } elseif ($detail->attendance_status === 'absent') {
                $groupsAttended[$groupId]['absent']++;
            } elseif ($detail->attendance_status === 'excused') {
                $groupsAttended[$groupId]['excused']++;
            }
        }
        
        return [
            'total_meetings' => $totalCount,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'excused_count' => $excusedCount,
            'attendance_rate' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0,
            'groups_attended' => array_values($groupsAttended)
        ];
    }
}