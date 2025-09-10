<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupMemberRoleController extends Controller
{
    /**
     * Get all members with their roles for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupMemberRoles(Request $request, $groupId)
    {
        $group = Group::find($groupId);
        
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }
        
        $members = $group->members()
            ->with('member')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'member_id' => $member->member_id,
                    'first_name' => $member->member->first_name,
                    'last_name' => $member->member->last_name,
                    'email' => $member->member->email,
                    'profile_photo' => $member->member->profile_photo,
                    'role' => $member->pivot->role,
                    'custom_role_title' => $member->pivot->custom_role_title,
                    'permissions' => $member->pivot->permissions,
                    'join_date' => $member->pivot->join_date,
                    'is_active' => $member->pivot->is_active
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $members
        ]);
    }
    
    /**
     * Update a member's role in a group.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMemberRole(Request $request, $groupId, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:leader,assistant_leader,secretary,treasurer,member,other',
            'custom_role_title' => 'nullable|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $group = Group::find($groupId);
        
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }
        
        $groupMember = GroupMember::where('group_id', $groupId)
            ->where('member_id', $memberId)
            ->first();
        
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member is not part of this group'
            ], 404);
        }
        
        // Update the member's role
        $groupMember->role = $request->input('role');
        
        // Update custom role title if role is 'other'
        if ($request->input('role') === 'other' && $request->has('custom_role_title')) {
            $groupMember->custom_role_title = $request->input('custom_role_title');
        } else {
            $groupMember->custom_role_title = null;
        }
        
        // Update permissions if provided
        if ($request->has('permissions')) {
            $groupMember->permissions = $request->input('permissions');
        }
        
        $groupMember->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Member role updated successfully',
            'data' => [
                'id' => $groupMember->id,
                'member_id' => $groupMember->member_id,
                'role' => $groupMember->role,
                'custom_role_title' => $groupMember->custom_role_title,
                'permissions' => $groupMember->permissions
            ]
        ]);
    }
    
    /**
     * Get available permissions for group roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailablePermissions()
    {
        $permissions = [
            [
                'value' => 'manage_attendance',
                'label' => 'Manage Attendance',
                'description' => 'Ability to record and manage attendance for group meetings'
            ],
            [
                'value' => 'manage_events',
                'label' => 'Manage Events',
                'description' => 'Ability to create, edit, and delete group events'
            ],
            [
                'value' => 'send_communications',
                'label' => 'Send Communications',
                'description' => 'Ability to send messages and announcements to group members'
            ],
            [
                'value' => 'manage_documents',
                'label' => 'Manage Documents',
                'description' => 'Ability to upload, edit, and delete group documents'
            ],
            [
                'value' => 'view_analytics',
                'label' => 'View Analytics',
                'description' => 'Ability to view group analytics and reports'
            ],
            [
                'value' => 'manage_members',
                'label' => 'Manage Members',
                'description' => 'Ability to add, remove, and manage group members'
            ],
            [
                'value' => 'manage_prayer_requests',
                'label' => 'Manage Prayer Requests',
                'description' => 'Ability to add, edit, and delete prayer requests'
            ],
            [
                'value' => 'manage_group_roles',
                'label' => 'Manage Group Roles',
                'description' => 'Ability to assign and modify roles and permissions for group members'
            ]
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $permissions
        ]);
    }
    
    /**
     * Get default permissions for a specific role.
     *
     * @param Request $request
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultPermissionsForRole(Request $request, $role)
    {
        $validRoles = ['leader', 'assistant_leader', 'secretary', 'treasurer', 'member', 'other'];
        
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role'
            ], 422);
        }
        
        // Define default permissions based on role
        $rolePermissions = [
            'leader' => [
                'manage_attendance', 'manage_events', 'send_communications', 
                'manage_documents', 'view_analytics', 'manage_members',
                'manage_prayer_requests', 'manage_group_roles'
            ],
            'assistant_leader' => [
                'manage_attendance', 'manage_events', 'send_communications', 
                'view_analytics', 'manage_prayer_requests'
            ],
            'secretary' => [
                'manage_attendance', 'send_communications', 'manage_documents', 
                'view_analytics'
            ],
            'treasurer' => [
                'manage_events', 'view_analytics'
            ],
            'member' => [
                'view_analytics'
            ],
            'other' => [
                'view_analytics'
            ]
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $rolePermissions[$role]
        ]);
    }
}
