<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupMessageRepositoryInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GroupMessageController extends Controller
{
    /**
     * @var GroupMessageRepositoryInterface
     */
    protected $groupMessageRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupMemberRepositoryInterface
     */
    protected $groupMemberRepository;

    /**
     * GroupMessageController constructor.
     *
     * @param GroupMessageRepositoryInterface $groupMessageRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupMemberRepositoryInterface $groupMemberRepository
     */
    public function __construct(
        GroupMessageRepositoryInterface $groupMessageRepository,
        GroupRepositoryInterface $groupRepository,
        GroupMemberRepositoryInterface $groupMemberRepository
    ) {
        $this->groupMessageRepository = $groupMessageRepository;
        $this->groupRepository = $groupRepository;
        $this->groupMemberRepository = $groupMemberRepository;
    }

    /**
     * Get all messages for a group.
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
            'is_announcement' => $request->input('is_announcement', null),
            'is_pinned' => $request->input('is_pinned', null),
            'message_type' => $request->input('message_type', null),
            'sender_id' => $request->input('sender_id', null),
            'search' => $request->input('search', null),
            'include_expired' => $request->input('include_expired', false),
        ];

        $perPage = $request->input('per_page', 15);
        $messages = $this->groupMessageRepository->getGroupMessages($groupId, $filters, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Get a specific message.
     *
     * @param int $groupId
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $messageId)
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

        // Get the message
        $message = $this->groupMessageRepository->getMessageById($messageId);
        if (!$message || $message->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found'
            ], 404);
        }

        // Mark message as read for this member
        $this->groupMessageRepository->markMessageAsRead($messageId, $member->id);

        return response()->json([
            'status' => 'success',
            'data' => $message
        ]);
    }

    /**
     * Create a new message.
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

        // Check permissions for announcements
        if ($request->input('is_announcement', false) && !$groupMember->hasPermission('create_announcements')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create announcements'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'message_type' => 'nullable|string|in:text,image,file',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'is_announcement' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'integer|exists:members,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle file upload if present
        $attachmentPath = null;
        $attachmentType = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $attachmentPath = $file->storeAs('group_messages', $fileName, 'public');
            $attachmentType = $file->getClientMimeType();
        }

        // Prepare data for message creation
        $messageData = [
            'group_id' => $groupId,
            'sender_id' => $member->id,
            'message' => $request->input('message'),
            'message_type' => $request->input('message_type', 'text'),
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'is_announcement' => $request->input('is_announcement', false),
            'is_pinned' => $request->input('is_pinned', false),
            'expires_at' => $request->input('expires_at'),
        ];

        // Add recipients if specified, otherwise add all group members
        if ($request->has('recipient_ids')) {
            $messageData['recipient_ids'] = $request->input('recipient_ids');
        } else {
            // Get all active group members
            $groupMembers = $this->groupMemberRepository->getActiveGroupMembers($groupId);
            $messageData['recipient_ids'] = $groupMembers->pluck('member_id')->toArray();
        }

        try {
            $message = $this->groupMessageRepository->createMessage($messageData);

            return response()->json([
                'status' => 'success',
                'message' => 'Message created successfully',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            // Delete the uploaded file if message creation fails
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                Storage::delete($attachmentPath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a message.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $messageId)
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

        // Get the message
        $message = $this->groupMessageRepository->getMessageById($messageId);
        if (!$message || $message->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found'
            ], 404);
        }

        // Check if user is the sender or has edit_messages permission
        if ($message->sender_id != $member->id && !$groupMember->hasPermission('edit_messages')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to edit this message'
            ], 403);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'is_announcement' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check permissions for announcements
        if ($request->has('is_announcement') && 
            $request->input('is_announcement') != $message->is_announcement && 
            !$groupMember->hasPermission('create_announcements')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to change announcement status'
            ], 403);
        }

        // Prepare data for message update
        $messageData = [];
        
        if ($request->has('message')) {
            $messageData['message'] = $request->input('message');
        }
        
        if ($request->has('is_announcement')) {
            $messageData['is_announcement'] = $request->input('is_announcement');
        }
        
        if ($request->has('is_pinned')) {
            $messageData['is_pinned'] = $request->input('is_pinned');
        }
        
        if ($request->has('expires_at')) {
            $messageData['expires_at'] = $request->input('expires_at');
        }

        try {
            $result = $this->groupMessageRepository->updateMessage($messageId, $messageData);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message updated successfully',
                    'data' => $this->groupMessageRepository->getMessageById($messageId)
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update message'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message.
     *
     * @param int $groupId
     * @param int $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $messageId)
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

        // Get the message
        $message = $this->groupMessageRepository->getMessageById($messageId);
        if (!$message || $message->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found'
            ], 404);
        }

        // Check if user is the sender or has delete_messages permission
        if ($message->sender_id != $member->id && !$groupMember->hasPermission('delete_messages')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this message'
            ], 403);
        }

        try {
            $result = $this->groupMessageRepository->deleteMessage($messageId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete message'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all announcements for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnouncements(Request $request, $groupId)
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

        $perPage = $request->input('per_page', 15);
        $announcements = $this->groupMessageRepository->getGroupAnnouncements($groupId, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $announcements
        ]);
    }

    /**
     * Get all pinned messages for a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPinnedMessages($groupId)
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

        $pinnedMessages = $this->groupMessageRepository->getPinnedMessages($groupId);

        return response()->json([
            'status' => 'success',
            'data' => $pinnedMessages
        ]);
    }

    /**
     * Get unread messages count for the current user in a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount($groupId)
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

        $count = $this->groupMessageRepository->getUnreadMessagesCount($groupId, $member->id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Mark all messages as read for the current user in a group.
     *
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead($groupId)
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

        $result = $this->groupMessageRepository->markAllMessagesAsRead($groupId, $member->id);

        if ($result) {
            return response()->json([
                'status' => 'success',
                'message' => 'All messages marked as read'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark messages as read'
            ], 500);
        }
    }
}
