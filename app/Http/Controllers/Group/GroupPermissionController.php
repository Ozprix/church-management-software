<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupPermissionRepositoryInterface;
use App\Repositories\Interfaces\GroupRolePermissionRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupPermissionController extends Controller
{
    /**
     * @var GroupPermissionRepositoryInterface
     */
    protected $groupPermissionRepository;

    /**
     * @var GroupRolePermissionRepositoryInterface
     */
    protected $groupRolePermissionRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * GroupPermissionController constructor.
     *
     * @param GroupPermissionRepositoryInterface $groupPermissionRepository
     * @param GroupRolePermissionRepositoryInterface $groupRolePermissionRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        GroupPermissionRepositoryInterface $groupPermissionRepository,
        GroupRolePermissionRepositoryInterface $groupRolePermissionRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->groupPermissionRepository = $groupPermissionRepository;
        $this->groupRolePermissionRepository = $groupRolePermissionRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Get all permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPermissions()
    {
        $permissions = $this->groupPermissionRepository->getAllPermissions();

        return response()->json([
            'status' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * Get permissions by category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionsByCategory($category)
    {
        $permissions = $this->groupPermissionRepository->getPermissionsByCategory($category);

        return response()->json([
            'status' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * Get all permission categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCategories()
    {
        $categories = $this->groupPermissionRepository->getAllCategories();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get all role permissions for a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupRolePermissions($groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $rolePermissions = $this->groupRolePermissionRepository->getGroupRolePermissions($groupId);

        return response()->json([
            'status' => 'success',
            'data' => $rolePermissions
        ]);
    }

    /**
     * Get permissions for a specific role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRolePermissions($groupId, $role)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Validate role
        $validRoles = ['leader', 'assistant_leader', 'secretary', 'treasurer', 'member', 'other'];
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role'
            ], 422);
        }

        $permissions = $this->groupRolePermissionRepository->getRolePermissions($groupId, $role);

        return response()->json([
            'status' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * Assign permissions to a role in a group.
     *
     * @param Request $request
     * @param int $groupId
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissionsToRole(Request $request, $groupId, $role)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Validate role
        $validRoles = ['leader', 'assistant_leader', 'secretary', 'treasurer', 'member', 'other'];
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role'
            ], 422);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'required|exists:group_permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->groupRolePermissionRepository->assignPermissionsToRole(
            $groupId,
            $role,
            $request->permission_ids
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign permissions to role'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Permissions assigned to role successfully'
        ]);
    }

    /**
     * Remove a specific permission from a role in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param int $permissionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePermissionFromRole($groupId, $role, $permissionId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Validate role
        $validRoles = ['leader', 'assistant_leader', 'secretary', 'treasurer', 'member', 'other'];
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role'
            ], 422);
        }

        // Check if permission exists
        $permission = $this->groupPermissionRepository->getPermissionById($permissionId);
        if (!$permission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found'
            ], 404);
        }

        $result = $this->groupRolePermissionRepository->removePermissionFromRole($groupId, $role, $permissionId);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove permission from role'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Permission removed from role successfully'
        ]);
    }

    /**
     * Check if a role has a specific permission in a group.
     *
     * @param int $groupId
     * @param string $role
     * @param string $permissionSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRolePermission($groupId, $role, $permissionSlug)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Validate role
        $validRoles = ['leader', 'assistant_leader', 'secretary', 'treasurer', 'member', 'other'];
        if (!in_array($role, $validRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role'
            ], 422);
        }

        // Check if permission exists
        $permission = $this->groupPermissionRepository->getPermissionBySlug($permissionSlug);
        if (!$permission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found'
            ], 404);
        }

        $hasPermission = $this->groupRolePermissionRepository->roleHasPermission($groupId, $role, $permissionSlug);

        return response()->json([
            'status' => 'success',
            'has_permission' => $hasPermission
        ]);
    }

    /**
     * Initialize default role permissions for a new group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function initializeDefaultRolePermissions($groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $result = $this->groupRolePermissionRepository->initializeDefaultRolePermissions($groupId);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initialize default role permissions'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Default role permissions initialized successfully'
        ]);
    }
}
