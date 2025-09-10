<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupPrayerRequestRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupPrayerRequestController extends Controller
{
    /**
     * @var GroupPrayerRequestRepositoryInterface
     */
    protected $groupPrayerRequestRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupMemberRepositoryInterface
     */
    protected $groupMemberRepository;

    /**
     * GroupPrayerRequestController constructor.
     *
     * @param GroupPrayerRequestRepositoryInterface $groupPrayerRequestRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupMemberRepositoryInterface $groupMemberRepository
     */
    public function __construct(
        GroupPrayerRequestRepositoryInterface $groupPrayerRequestRepository,
        GroupRepositoryInterface $groupRepository,
        GroupMemberRepositoryInterface $groupMemberRepository
    ) {
        $this->groupPrayerRequestRepository = $groupPrayerRequestRepository;
        $this->groupRepository = $groupRepository;
        $this->groupMemberRepository = $groupMemberRepository;
    }

    /**
     * Get all prayer requests for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get filters from request
        $filters = [
            'status' => $request->input('status', 'active'),
            'member_id' => $request->input('member_id'),
            'is_anonymous' => $request->input('is_anonymous'),
            'is_private' => $request->input('is_private'),
            'search' => $request->input('search'),
            'order_by' => $request->input('order_by'),
            'order_direction' => $request->input('order_direction'),
        ];

        // If user doesn't have view_private_prayers permission, force is_private to false
        if (!$groupMember->hasPermission('view_private_prayers')) {
            $filters['is_private'] = false;
        }

        $perPage = $request->input('per_page', 15);
        $prayerRequests = $this->groupPrayerRequestRepository->getGroupPrayerRequests($groupId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $prayerRequests
        ]);
    }

    /**
     * Get a specific prayer request.
     *
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user can view private prayer request
        if ($prayerRequest->is_private && 
            $prayerRequest->member_id != $member->id && 
            !$groupMember->hasPermission('view_private_prayers')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this prayer request'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $prayerRequest
        ]);
    }

    /**
     * Create a new prayer request.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_anonymous' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for prayer request creation
        $prayerRequestData = [
            'group_id' => $groupId,
            'member_id' => $member->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_anonymous' => $request->input('is_anonymous', false),
            'is_private' => $request->input('is_private', false),
        ];

        try {
            $prayerRequest = $this->groupPrayerRequestRepository->createPrayerRequest($prayerRequestData);

            return response()->json([
                'status' => 'success',
                'message' => 'Prayer request created successfully',
                'data' => $prayerRequest
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create prayer request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a prayer request.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user is the creator or has manage_prayer_requests permission
        if ($prayerRequest->member_id != $member->id && !$groupMember->hasPermission('manage_prayer_requests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this prayer request'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_anonymous' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for prayer request update
        $prayerRequestData = [];
        
        if ($request->has('title')) {
            $prayerRequestData['title'] = $request->input('title');
        }
        
        if ($request->has('description')) {
            $prayerRequestData['description'] = $request->input('description');
        }
        
        if ($request->has('is_anonymous')) {
            $prayerRequestData['is_anonymous'] = $request->input('is_anonymous');
        }
        
        if ($request->has('is_private')) {
            $prayerRequestData['is_private'] = $request->input('is_private');
        }

        try {
            $result = $this->groupPrayerRequestRepository->updatePrayerRequest($requestId, $prayerRequestData);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Prayer request updated successfully',
                    'data' => $this->groupPrayerRequestRepository->getPrayerRequestById($requestId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update prayer request'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update prayer request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a prayer request.
     *
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user is the creator or has manage_prayer_requests permission
        if ($prayerRequest->member_id != $member->id && !$groupMember->hasPermission('manage_prayer_requests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this prayer request'
            ], 403);
        }

        try {
            $result = $this->groupPrayerRequestRepository->deletePrayerRequest($requestId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Prayer request deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete prayer request'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete prayer request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a prayer request as answered.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsAnswered(Request $request, $groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user is the creator or has manage_prayer_requests permission
        if ($prayerRequest->member_id != $member->id && !$groupMember->hasPermission('manage_prayer_requests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to mark this prayer request as answered'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'answer_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $answerDescription = $request->input('answer_description');
            $result = $this->groupPrayerRequestRepository->markAsAnswered($requestId, $answerDescription);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Prayer request marked as answered',
                    'data' => $this->groupPrayerRequestRepository->getPrayerRequestById($requestId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to mark prayer request as answered'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark prayer request as answered',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive a prayer request.
     *
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive($groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user is the creator or has manage_prayer_requests permission
        if ($prayerRequest->member_id != $member->id && !$groupMember->hasPermission('manage_prayer_requests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to archive this prayer request'
            ], 403);
        }

        try {
            $result = $this->groupPrayerRequestRepository->archivePrayerRequest($requestId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Prayer request archived',
                    'data' => $this->groupPrayerRequestRepository->getPrayerRequestById($requestId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to archive prayer request'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to archive prayer request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a prayer request.
     *
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reactivate($groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Check if user is the creator or has manage_prayer_requests permission
        if ($prayerRequest->member_id != $member->id && !$groupMember->hasPermission('manage_prayer_requests')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to reactivate this prayer request'
            ], 403);
        }

        try {
            $result = $this->groupPrayerRequestRepository->reactivatePrayerRequest($requestId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Prayer request reactivated',
                    'data' => $this->groupPrayerRequestRepository->getPrayerRequestById($requestId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to reactivate prayer request'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reactivate prayer request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a prayer response to a prayer request.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addResponse(Request $request, $groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'response' => 'required|string',
            'is_praying' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $response = $request->input('response');
            $isPraying = $request->input('is_praying', true);
            
            $result = $this->groupPrayerRequestRepository->addPrayerResponse($requestId, $member->id, $response, $isPraying);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Response added successfully',
                    'data' => $this->groupPrayerRequestRepository->getPrayerRequestById($requestId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to add response'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle praying status for a prayer request.
     *
     * @param int $groupId
     * @param int $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function togglePraying($groupId, $requestId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        // Get the prayer request
        $prayerRequest = $this->groupPrayerRequestRepository->getPrayerRequestById($requestId);
        if (!$prayerRequest || $prayerRequest->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prayer request not found'
            ], 404);
        }

        try {
            $result = $this->groupPrayerRequestRepository->togglePraying($requestId, $member->id);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Praying status toggled successfully',
                    'data' => [
                        'is_praying' => $prayerRequest->isPrayingBy($member->id)
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to toggle praying status'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle praying status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prayer requests statistics for a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats($groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if user is a member of the group
        $member = Auth::user()->member;
        $groupMember = $this->groupMemberRepository->getGroupMember($groupId, $member->id);
        if (!$groupMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group'
            ], 403);
        }

        $stats = $this->groupPrayerRequestRepository->getPrayerRequestsStats($groupId);

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
