<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'parent_id',
        'leader_id',
        'meeting_day',
        'meeting_time',
        'meeting_location',
        'meeting_frequency',
        'target_size',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'target_size' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['member_count', 'next_meeting_date'];

    /**
     * The default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the parent group.
     */
    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    /**
     * Get the child groups.
     */
    public function children()
    {
        return $this->hasMany(Group::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendant groups.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the leader of the group.
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id');
    }

    /**
     * Get the members of the group.
     * The members that belong to the group.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'group_member')
            ->withPivot('role', 'joined_at', 'left_at', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the active members of the group.
     */
    public function activeMembers()
    {
        return $this->members()->wherePivot('is_active', true)->wherePivot('exit_date', null);
    }

    /**
     * Get the count of active members in the group.
     */
    public function getActiveMemberCountAttribute()
    {
        return $this->activeMembers()->count();
    }

    /**
     * Scope a query to only include active groups.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by group type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Get the attendance records for this group.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(GroupAttendance::class);
    }
    
    /**
     * Get the analytics for the group.
     */
    public function analytics()
    {
        return $this->hasMany(GroupAnalytics::class);
    }
    
    /**
     * Get the events for this group.
     */
    public function events(): HasMany
    {
        return $this->hasMany(GroupEvent::class);
    }
    
    /**
     * Get the messages for the group.
     */
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }
    
    /**
     * Get the prayer requests for the group.
     */
    public function prayerRequests()
    {
        return $this->hasMany(GroupPrayerRequest::class);
    }
    
    /**
     * Get the documents for the group.
     */
    public function documents()
    {
        return $this->hasMany(GroupDocument::class);
    }
    
    /**
     * Get recent attendance records for this group.
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentAttendances($limit = 5)
    {
        return $this->attendances()
            ->orderBy('attendance_date', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get the attendance statistics for this group.
     * 
     * @param string $period
     * @return array
     */
    public function getAttendanceStats($period = '3months')
    {
        $query = $this->attendances();
        
        // Filter by period
        if ($period === '1month') {
            $query->where('attendance_date', '>=', now()->subMonth());
        } elseif ($period === '3months') {
            $query->where('attendance_date', '>=', now()->subMonths(3));
        } elseif ($period === '6months') {
            $query->where('attendance_date', '>=', now()->subMonths(6));
        } elseif ($period === '1year') {
            $query->where('attendance_date', '>=', now()->subYear());
        }
        
        $attendances = $query->get();
        
        if ($attendances->isEmpty()) {
            return [
                'total_meetings' => 0,
                'avg_attendance' => 0,
                'avg_visitors' => 0,
                'total_first_timers' => 0,
                'attendance_trend' => []
            ];
        }
        
        return [
            'total_meetings' => $attendances->count(),
            'avg_attendance' => round($attendances->avg('total_attendees'), 1),
            'avg_visitors' => round($attendances->avg('total_visitors'), 1),
            'total_first_timers' => $attendances->sum('total_first_timers'),
            'attendance_trend' => $attendances->sortBy('attendance_date')->pluck('total_attendees', 'attendance_date')
        ];
    }
    
    /**
     * Get upcoming events for this group.
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function upcomingEvents($limit = 5)
    {
        return $this->events()
            ->where('event_date', '>=', now()->format('Y-m-d'))
            ->where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get the role permissions for this group.
     */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(GroupRolePermission::class);
    }
    
    /**
     * Get all permissions assigned to a specific role in this group.
     *
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissionsForRole($role)
    {
        $cacheKey = "group_{$this->id}_role_{$role}_permissions";
        
        return Cache::remember($cacheKey, 60 * 5, function () use ($role) {
            return GroupPermission::whereIn('id', function ($query) use ($role) {
                $query->select('permission_id')
                    ->from('group_role_permissions')
                    ->where('group_id', $this->id)
                    ->where('role', $role);
            })->get();
        });
    }
    
    /**
     * Assign permissions to a role in this group.
     *
     * @param string $role
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissionsToRole($role, array $permissionIds)
    {
        try {
            // Begin transaction
            DB::beginTransaction();
            
            // Remove existing permissions for this role
            GroupRolePermission::where('group_id', $this->id)
                ->where('role', $role)
                ->delete();
            
            // Add new permissions
            $rolePermissions = [];
            foreach ($permissionIds as $permissionId) {
                $rolePermissions[] = [
                    'group_id' => $this->id,
                    'role' => $role,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            GroupRolePermission::insert($rolePermissions);
            
            // Commit transaction
            DB::commit();
            
            // Clear cache
            Cache::forget("group_{$this->id}_role_{$role}_permissions");
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return false;
        }
    }
    
    /**
     * Check if a role has a specific permission in this group.
     *
     * @param string $role
     * @param string $permissionSlug
     * @return bool
     */
    public function roleHasPermission($role, $permissionSlug)
    {
        // Leaders always have all permissions
        if ($role === 'leader') {
            return true;
        }
        
        $cacheKey = "group_{$this->id}_role_{$role}_has_permission_{$permissionSlug}";
        
        return Cache::remember($cacheKey, 60 * 5, function () use ($role, $permissionSlug) {
            return GroupRolePermission::where('group_id', $this->id)
                ->where('role', $role)
                ->whereHas('permission', function ($query) use ($permissionSlug) {
                    $query->where('slug', $permissionSlug);
                })
                ->exists();
        });
    }
    
    /**
     * Get all members with a specific role in this group.
     *
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMembersByRole($role)
    {
        return $this->members()
            ->wherePivot('role', $role)
            ->wherePivot('is_active', true)
            ->wherePivot('exit_date', null)
            ->get();
    }
    
    /**
     * Get comprehensive analytics data for the group.
     *
     * @param string $timeRange
     * @return array
     */
    public function getAnalyticsData($timeRange = 'month')
    {
        // Set date range based on the selected time range
        $startDate = now();
        switch ($timeRange) {
            case 'month':
                $startDate = $startDate->subMonth();
                break;
            case 'quarter':
                $startDate = $startDate->subMonths(3);
                break;
            case 'year':
                $startDate = $startDate->subYear();
                break;
            default:
                $startDate = $startDate->subMonth();
        }
        
        // Get total members
        $totalMembers = $this->activeMembers()->count();
        
        // Get new members in the time range
        $newMembers = $this->members()
            ->wherePivot('join_date', '>=', $startDate)
            ->wherePivot('is_active', true)
            ->count();
        
        // Get attendance data
        $attendanceData = $this->attendances()
            ->where('attendance_date', '>=', $startDate)
            ->orderBy('attendance_date')
            ->get();
        
        // Calculate average attendance percentage
        $avgAttendance = 0;
        if ($attendanceData->count() > 0) {
            $avgAttendance = round(
                $attendanceData->sum('attendance_count') / 
                ($attendanceData->count() * $totalMembers) * 100
            );
        }
        
        // Get total events in the time range
        $totalEvents = $this->events()
            ->where('start_time', '>=', $startDate)
            ->count();
        
        // Get member demographics
        $members = $this->activeMembers()->get();
        $ageGroups = [
            '18-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55+' => 0
        ];
        $gender = [
            'Male' => 0,
            'Female' => 0
        ];
        
        foreach ($members as $member) {
            // Calculate age group
            if ($member->date_of_birth) {
                $age = $member->date_of_birth->age;
                if ($age < 25) {
                    $ageGroups['18-24']++;
                } elseif ($age < 35) {
                    $ageGroups['25-34']++;
                } elseif ($age < 45) {
                    $ageGroups['35-44']++;
                } elseif ($age < 55) {
                    $ageGroups['45-54']++;
                } else {
                    $ageGroups['55+']++;
                }
            }
            
            // Count gender
            if ($member->gender) {
                if (strtolower($member->gender) === 'male') {
                    $gender['Male']++;
                } elseif (strtolower($member->gender) === 'female') {
                    $gender['Female']++;
                }
            }
        }
        
        // Convert counts to percentages
        if ($totalMembers > 0) {
            foreach ($ageGroups as $group => $count) {
                $ageGroups[$group] = round(($count / $totalMembers) * 100);
            }
            
            foreach ($gender as $type => $count) {
                $gender[$type] = round(($count / $totalMembers) * 100);
            }
        }
        
        return [
            'stats' => [
                'totalMembers' => $totalMembers,
                'avgAttendance' => $avgAttendance,
                'newMembers' => $newMembers,
                'totalEvents' => $totalEvents
            ],
            'demographics' => [
                'ageGroups' => $ageGroups,
                'gender' => $gender
            ],
            'attendanceData' => $attendanceData,
            'timeRange' => $timeRange
        ];
    }
    
    /**
     * Get the integration settings for this group.
     */
    public function integrationSettings(): HasMany
    {
        return $this->hasMany(IntegrationSetting::class);
    }
    
    /**
     * Get Google Calendar integration settings for this group.
     */
    public function googleCalendarIntegrations(): HasMany
    {
        return $this->integrationSettings()->where('integration_type', 'google_calendar');
    }
    
    /**
     * Get SMS integration settings for this group.
     */
    public function smsIntegrations(): HasMany
    {
        return $this->integrationSettings()->where('integration_type', 'sms');
    }
    
    /**
     * Initialize default role permissions for this group.
     *
     * @return bool
     */
    public function initializeDefaultRolePermissions()
    {
        try {
            // Begin transaction
            DB::beginTransaction();
            
            // Get all permissions
            $allPermissions = GroupPermission::all();
            
            // Default role permissions
            $rolePermissions = [
                'leader' => $allPermissions->pluck('id')->toArray(), // Leaders get all permissions
                
                'assistant_leader' => $allPermissions->filter(function ($permission) {
                    return $permission->slug !== 'manage-role-permissions' && 
                           $permission->slug !== 'edit-group-settings';
                })->pluck('id')->toArray(),
                
                'secretary' => $allPermissions->filter(function ($permission) {
                    return in_array($permission->category, ['members', 'attendance', 'events', 'communications']) &&
                           !in_array($permission->slug, ['remove-members', 'edit-member-roles', 'delete-events']);
                })->pluck('id')->toArray(),
                
                'treasurer' => $allPermissions->filter(function ($permission) {
                    return in_array($permission->category, ['members', 'attendance']) ||
                           $permission->slug === 'view-events' ||
                           $permission->slug === 'view-analytics' ||
                           $permission->slug === 'export-reports';
                })->pluck('id')->toArray(),
                
                'member' => $allPermissions->filter(function ($permission) {
                    return in_array($permission->slug, [
                        'view-members', 'view-attendance', 'view-events',
                        'view-messages', 'view-analytics'
                    ]);
                })->pluck('id')->toArray(),
            ];
            
            // Insert role permissions
            foreach ($rolePermissions as $role => $permissionIds) {
                foreach ($permissionIds as $permissionId) {
                    GroupRolePermission::create([
                        'group_id' => $this->id,
                        'role' => $role,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
            
            // Commit transaction
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return false;
        }
    }
}
