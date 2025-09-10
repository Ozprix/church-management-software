<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupRolePermission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'role',
        'permission_id',
    ];

    /**
     * Get the group that this role permission belongs to.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the permission that this role permission belongs to.
     */
    public function permission()
    {
        return $this->belongsTo(GroupPermission::class, 'permission_id');
    }
}
