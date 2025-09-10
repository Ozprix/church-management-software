<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\GroupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupMemberController extends Controller
{
    /**
     * @var GroupServiceInterface
     */
    protected $groupService;

    /**
     * GroupMemberController constructor.
     *
     * @param GroupServiceInterface $groupService
     */
    public function __construct(GroupServiceInterface $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Display a listing of the members in a group.
     *
     * @param string $groupId
     * @return JsonResponse
     */
    public function index(string $groupId): JsonResponse
    {
        $members = $this->groupService->getGroupMembers($groupId);
        
        return response()->json([
            'status' => 'success',
            'data' => $members
        ]);
    }

    /**
     * Add a member to a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return JsonResponse
     */
    public function store(Request $request, string $groupId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'role' => 'nullable|in:member,leader,assistant_leader,secretary,treasurer,other',
            'join_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->groupService->addMemberToGroup($groupId, $request->member_id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Member added to group successfully'
        ], 201);
    }

    /**
     * Update a member's role in a group.
     *
     * @param Request $request
     * @param string $groupId
     * @param string $memberId
     * @return JsonResponse
     */
    public function update(Request $request, string $groupId, string $memberId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'nullable|in:member,leader,assistant_leader,secretary,treasurer,other',
            'exit_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $this->groupService->updateMemberRole($groupId, $memberId, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Member role updated successfully'
        ]);
    }

    /**
     * Remove a member from a group.
     *
     * @param string $groupId
     * @param string $memberId
     * @return JsonResponse
     */
    public function destroy(string $groupId, string $memberId): JsonResponse
    {
        $this->groupService->removeMemberFromGroup($groupId, $memberId);

        return response()->json([
            'status' => 'success',
            'message' => 'Member removed from group successfully'
        ]);
    }

    /**
     * Batch add members to a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return JsonResponse
     */
    public function batchStore(Request $request, string $groupId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'members' => 'required|array',
            'members.*.member_id' => 'required|exists:members,id',
            'members.*.role' => 'nullable|in:member,leader,assistant_leader,secretary,treasurer,other',
            'members.*.join_date' => 'nullable|date',
            'members.*.notes' => 'nullable|string',
            'members.*.is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = true;
        $errors = [];

        foreach ($request->members as $memberData) {
            try {
                $this->groupService->addMemberToGroup($groupId, $memberData['member_id'], $memberData);
            } catch (\Exception $e) {
                $success = false;
                $errors[] = [
                    'member_id' => $memberData['member_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'Members added to group successfully'
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Some members could not be added to the group',
                'errors' => $errors
            ], 422);
        }
    }
}
