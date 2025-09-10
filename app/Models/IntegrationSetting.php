<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'integration_type',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_active',
        'settings',
        'preferences',
        'group_id',
        'last_synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
        'preferences' => 'array',
    ];

    /**
     * Get the user that owns the integration setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the group associated with the integration setting.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Scope a query to only include active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include integrations of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('integration_type', $type);
    }

    /**
     * Check if the integration token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    /**
     * Check if this is a Google Calendar integration.
     */
    public function isGoogleCalendar(): bool
    {
        return $this->integration_type === 'google_calendar';
    }

    /**
     * Check if this is an SMS integration.
     */
    public function isSms(): bool
    {
        return $this->integration_type === 'sms';
    }
}
