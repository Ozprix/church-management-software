<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventResource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_event_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'external_url',
        'resource_type',
        'is_public',
        'uploaded_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the event that owns the resource.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(GroupEvent::class, 'group_event_id');
    }

    /**
     * Get the user who uploaded the resource.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL for the resource file.
     *
     * @return string|null
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return url('storage/' . $this->file_path);
    }

    /**
     * Get the formatted file size.
     *
     * @return string|null
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $size = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Get the resource URL (either file URL or external URL).
     *
     * @return string|null
     */
    public function getResourceUrlAttribute(): ?string
    {
        return $this->external_url ?: $this->file_url;
    }

    /**
     * Scope a query to only include public resources.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to filter by resource type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('resource_type', $type);
    }
}
