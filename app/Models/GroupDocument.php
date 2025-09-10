<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GroupDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'uploaded_by',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'is_public',
        'download_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'file_url',
        'file_size_formatted',
    ];

    /**
     * Get the group that owns the document.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the member that uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(Member::class, 'uploaded_by');
    }

    /**
     * Get the access records for the document.
     */
    public function accessRecords()
    {
        return $this->hasMany(GroupDocumentAccess::class, 'document_id');
    }

    /**
     * Get the file URL.
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return url(Storage::url($this->file_path));
    }

    /**
     * Get the formatted file size.
     *
     * @return string
     */
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Increment the download count.
     *
     * @return bool
     */
    public function incrementDownloadCount(): bool
    {
        $this->download_count++;
        return $this->save();
    }

    /**
     * Record access by a member.
     *
     * @param int $memberId
     * @return bool
     */
    public function recordAccess(int $memberId): bool
    {
        $access = $this->accessRecords()->firstOrNew([
            'member_id' => $memberId,
        ]);
        
        $access->last_accessed_at = now();
        $access->access_count++;
        
        return $access->save();
    }

    /**
     * Check if the document has been accessed by a member.
     *
     * @param int $memberId
     * @return bool
     */
    public function hasBeenAccessedBy(int $memberId): bool
    {
        return $this->accessRecords()->where('member_id', $memberId)->exists();
    }

    /**
     * Scope a query to only include public documents.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
