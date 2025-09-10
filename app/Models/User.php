<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the member associated with the user.
     */
    public function member()
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Get the events created by the user.
     */
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get the expenses approved by the user.
     */
    public function approvedExpenses()
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    /**
     * Get the communications sent by the user.
     */
    public function sentCommunications()
    {
        return $this->hasMany(Communication::class, 'sender_id');
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAnyPermission($permissions);
    }

    /**
     * Check if the user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasAllPermissions($permissions);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'Admin';
    }

    /**
     * Check if the user is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->role && $this->role->name === 'Super Admin';
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }
    
    /**
     * Get all integration settings for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrationSettings(): HasMany
    {
        return $this->hasMany(IntegrationSetting::class);
    }
    
    /**
     * Get Google Calendar integration settings for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function googleCalendarIntegrations(): HasMany
    {
        return $this->integrationSettings()->where('integration_type', 'google_calendar');
    }
    
    /**
     * Get SMS integration settings for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function smsIntegrations(): HasMany
    {
        return $this->integrationSettings()->where('integration_type', 'sms');
    }
}
