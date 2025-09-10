<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\GroupMessageRecipient;
use App\Models\GroupPrayerRequest;
use App\Models\GroupPrayerResponse;
use App\Models\GroupDocumentAccess;
use App\Models\Group;
use App\Models\Member;

class GroupMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'member_id',
        'role',
        'custom_role_title',
        'permissions',
        'join_date',
        'exit_date',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'join_date' => 'date',
        'exit_date' => 'date',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    /**
     * Get the group that the membership belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the member that owns the group membership.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    
    /**
     * Get the message recipients for this group member.
     */
    public function messageRecipients()
    {
        return $this->hasMany(GroupMessageRecipient::class, 'member_id', 'member_id')
            ->whereHas('message', function($query) {
                $query->where('group_id', $this->group_id);
            });
    }
    
    /**
     * Get the prayer requests created by this group member.
     */
    public function prayerRequests()
    {
        return $this->hasMany(GroupPrayerRequest::class, 'member_id', 'member_id')
            ->where('group_id', $this->group_id);
    }
    
    /**
     * Get the prayer responses by this group member.
     */
    public function prayerResponses()
    {
        return $this->hasMany(GroupPrayerResponse::class, 'member_id', 'member_id')
            ->whereHas('prayerRequest', function($query) {
                $query->where('group_id', $this->group_id);
            });
    }
    
    /**
     * Get the document access records for this group member.
     */
    public function documentAccesses()
    {
        return $this->hasMany(GroupDocumentAccess::class, 'member_id', 'member_id')
            ->whereHas('document', function($query) {
                $query->where('group_id', $this->group_id);
            });
    }

    /**
     * Scope a query to only include active memberships.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('exit_date');
    }
    
    /**
     * Get all permissions for this member in this group.
     *
     * @return array
     */
    public function getPermissions()
    {
        // If permissions are directly stored on the model, return those
        if (!empty($this->permissions)) {
            return $this->permissions;
        }
        
        // Otherwise, fall back to role-based permissions
        $cacheKey = "group_member_{$this->id}_permissions";
        
        return Cache::remember($cacheKey, 60 * 5, function () {
            // Define default permissions based on role
            $rolePermissions = [
                'leader' => [
                    'manage_attendance', 'manage_events', 'send_communications', 
                    'manage_documents', 'view_analytics', 'manage_members'
                ],
                'assistant_leader' => [
                    'manage_attendance', 'manage_events', 'send_communications', 'view_analytics'
                ],
                'secretary' => [
                    'manage_attendance', 'send_communications', 'manage_documents', 'view_analytics'
                ],
                'treasurer' => [
                    'manage_events', 'view_analytics'
                ],
                'member' => [
                    'view_analytics'
                ]
            ];
            
            return $rolePermissions[$this->role] ?? ['view_analytics'];
        });
    }
    
    /**
     * Check if the member has a specific permission in this group.
     *
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($permissionSlug)
    {
        // Leaders always have all permissions
        if ($this->role === 'leader') {
            return true;
        }
        
        // Check directly stored permissions first
        if (!empty($this->permissions)) {
            return in_array($permissionSlug, $this->permissions);
        }
        
        // Fall back to role-based permissions
        $permissions = $this->getPermissions();
        return in_array($permissionSlug, $permissions);
    }
    
    /**
     * Check if the member has any of the given permissions in this group.
     *
     * @param array $permissionSlugs
     * @return bool
     */
    public function hasAnyPermission(array $permissionSlugs)
    {
        // Leaders always have all permissions
        if ($this->role === 'leader') {
            return true;
        }
        
        // Check directly stored permissions first
        if (!empty($this->permissions)) {
            return count(array_intersect($permissionSlugs, $this->permissions)) > 0;
        }
        
        // Fall back to role-based permissions
        $permissions = $this->getPermissions();
        return count(array_intersect($permissionSlugs, $permissions)) > 0;
    }
    
    /**
     * Check if the member has all of the given permissions in this group.
     *
     * @param array $permissionSlugs
     * @return bool
     */
    public function hasAllPermissions(array $permissionSlugs)
    {
        // Leaders always have all permissions
        if ($this->role === 'leader') {
            return true;
        }
        
        // Check directly stored permissions first
        if (!empty($this->permissions)) {
            $intersection = array_intersect($permissionSlugs, $this->permissions);
            return count($intersection) === count($permissionSlugs);
        }
        
        // Fall back to role-based permissions
        $permissions = $this->getPermissions();
        $intersection = array_intersect($permissionSlugs, $permissions);
        return count($intersection) === count($permissionSlugs);
    }
}
