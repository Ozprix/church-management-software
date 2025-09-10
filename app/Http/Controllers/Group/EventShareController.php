<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventShareRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\MemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventShareController extends Controller
{
    /**
     * @var EventShareRepositoryInterface
     */
    protected $eventShareRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    /**
     * EventShareController constructor.
     *
     * @param EventShareRepositoryInterface $eventShareRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param MemberRepositoryInterface $memberRepository
     */
    public function __construct(
        EventShareRepositoryInterface $eventShareRepository,
        GroupEventRepositoryInterface $groupEventRepository,
        GroupRepositoryInterface $groupRepository,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->eventShareRepository = $eventShareRepository;
        $this->groupEventRepository = $groupEventRepository;
        $this->groupRepository = $groupRepository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * Display a listing of the shares for an event.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $shares = $this->eventShareRepository->getEventShares($eventId);

        return response()->json([
            'status' => 'success',
            'data' => $shares
        ]);
    }

    /**
     * Store a newly created share in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'share_type' => 'required|string|in:group,member,email',
            'shared_with_group_id' => 'required_if:share_type,group|nullable|exists:groups,id',
            'shared_with_member_id' => 'required_if:share_type,member|nullable|exists:members,id',
            'shared_with_email' => 'required_if:share_type,email|nullable|email|max:255',
            'message' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate that we're not sharing with the same group
        if ($request->share_type === 'group' && $request->shared_with_group_id == $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot share an event with its own group'
            ], 422);
        }

        try {
            $data = $request->all();
            $data['group_event_id'] = $eventId;
            $data['shared_by'] = Auth::id();
            $data['status'] = 'pending';
            $data['token'] = Str::random(32);
            
            // Set default expiration if not provided
            if (!isset($data['expires_at'])) {
                $data['expires_at'] = now()->addDays(7);
            }

            $share = $this->eventShareRepository->createShare($data);

            // Send invitation
            app(\App\Services\Interfaces\EventShareServiceInterface::class)->sendShareInvitation($share);

            return response()->json([
                'status' => 'success',
                'message' => 'Event shared successfully',
                'data' => $share
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to share event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified share.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $shareId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $eventId, $shareId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $share = $this->eventShareRepository->getShareById($shareId);

        if (!$share || $share->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $share
        ]);
    }

    /**
     * Update the specified share in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @param int $shareId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $eventId, $shareId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if share exists and belongs to the event
        $share = $this->eventShareRepository->getShareById($shareId);
        if (!$share || $share->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:pending,accepted,declined',
            'expires_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
            // Set timestamps based on status changes
            if ($request->has('status')) {
                if ($request->status === 'accepted' && $share->status !== 'accepted') {
                    $data['accepted_at'] = now();
                } elseif ($request->status === 'declined' && $share->status !== 'declined') {
                    $data['declined_at'] = now();
                }
            }

            $updatedShare = $this->eventShareRepository->updateShare($shareId, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Share updated successfully',
                'data' => $updatedShare
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update share',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified share from storage.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $shareId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $eventId, $shareId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if share exists and belongs to the event
        $share = $this->eventShareRepository->getShareById($shareId);
        if (!$share || $share->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Share not found'
            ], 404);
        }

        try {
            $result = $this->eventShareRepository->deleteShare($shareId);

            return response()->json([
                'status' => 'success',
                'message' => 'Share deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete share',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shares by status for an event.
     *
     * @param int $groupId
     * @param int $eventId
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSharesByStatus($groupId, $eventId, $status)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Validate status
        if (!in_array($status, ['pending', 'accepted', 'declined'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid status. Must be one of: pending, accepted, declined'
            ], 422);
        }

        $shares = $this->eventShareRepository->getSharesByStatus($eventId, $status);

        return response()->json([
            'status' => 'success',
            'data' => $shares
        ]);
    }

    /**
     * Get shares for a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSharesForGroup($groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $shares = $this->eventShareRepository->getSharesForGroup($groupId);

        return response()->json([
            'status' => 'success',
            'data' => $shares
        ]);
    }

    /**
     * Get shares for a member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSharesForMember($memberId)
    {
        // Check if member exists
        $member = $this->memberRepository->getMemberById($memberId);
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $shares = $this->eventShareRepository->getSharesForMember($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $shares
        ]);
    }

    /**
     * Accept a share invitation.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function acceptShare($token)
    {
        $share = $this->eventShareRepository->getShareByToken($token);

        if (!$share) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired share token'
            ], 404);
        }

        if ($share->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This share has already been ' . $share->status
            ], 400);
        }

        if ($share->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This share invitation has expired'
            ], 400);
        }

        try {
            $result = $this->eventShareRepository->acceptShare($token);

            return response()->json([
                'status' => 'success',
                'message' => 'Share accepted successfully',
                'data' => $this->eventShareRepository->getShareByToken($token)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to accept share',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decline a share invitation.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function declineShare($token)
    {
        $share = $this->eventShareRepository->getShareByToken($token);

        if (!$share) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired share token'
            ], 404);
        }

        if ($share->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This share has already been ' . $share->status
            ], 400);
        }

        if ($share->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This share invitation has expired'
            ], 400);
        }

        try {
            $result = $this->eventShareRepository->declineShare($token);

            return response()->json([
                'status' => 'success',
                'message' => 'Share declined successfully',
                'data' => $this->eventShareRepository->getShareByToken($token)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to decline share',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a shared event by token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewSharedEvent($token)
    {
        $share = $this->eventShareRepository->getShareByToken($token);

        if (!$share) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired share token'
            ], 404);
        }

        if ($share->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This share invitation has expired'
            ], 400);
        }

        $event = $this->groupEventRepository->getGroupEventById($share->group_event_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'share' => $share,
                'event' => $event
            ]
        ]);
    }
}
