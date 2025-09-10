<?php

namespace App\Repositories;

use App\Models\GroupMessage;
use App\Models\GroupMessageRecipient;
use App\Repositories\Interfaces\GroupMessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GroupMessageRepository implements GroupMessageRepositoryInterface
{
    /**
     * Get all messages for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = GroupMessage::where('group_id', $groupId)
            ->with(['sender', 'recipients'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if (isset($filters['is_announcement']) && $filters['is_announcement']) {
            $query->where('is_announcement', true);
        }
        
        if (isset($filters['is_pinned']) && $filters['is_pinned']) {
            $query->where('is_pinned', true);
        }
        
        if (isset($filters['message_type'])) {
            $query->where('message_type', $filters['message_type']);
        }
        
        if (isset($filters['sender_id'])) {
            $query->where('sender_id', $filters['sender_id']);
        }
        
        if (isset($filters['search'])) {
            $query->where('message', 'like', '%' . $filters['search'] . '%');
        }
        
        // Only show active (non-expired) messages
        if (!isset($filters['include_expired']) || !$filters['include_expired']) {
            $query->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get a message by ID.
     *
     * @param int $messageId
     * @return GroupMessage|null
     */
    public function getMessageById(int $messageId): ?GroupMessage
    {
        return GroupMessage::with(['sender', 'recipients.member'])->find($messageId);
    }

    /**
     * Create a new message.
     *
     * @param array $data
     * @return GroupMessage
     */
    public function createMessage(array $data): GroupMessage
    {
        try {
            DB::beginTransaction();
            
            // Create the message
            $message = GroupMessage::create([
                'group_id' => $data['group_id'],
                'sender_id' => $data['sender_id'],
                'message' => $data['message'],
                'message_type' => $data['message_type'] ?? 'text',
                'attachment_path' => $data['attachment_path'] ?? null,
                'attachment_type' => $data['attachment_type'] ?? null,
                'is_announcement' => $data['is_announcement'] ?? false,
                'is_pinned' => $data['is_pinned'] ?? false,
                'expires_at' => $data['expires_at'] ?? null,
            ]);
            
            // Add recipients if provided
            if (isset($data['recipient_ids']) && is_array($data['recipient_ids'])) {
                $this->addMessageRecipients($message->id, $data['recipient_ids']);
            }
            
            DB::commit();
            
            return $message;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a message.
     *
     * @param int $messageId
     * @param array $data
     * @return bool
     */
    public function updateMessage(int $messageId, array $data): bool
    {
        $message = $this->getMessageById($messageId);
        
        if (!$message) {
            return false;
        }
        
        return $message->update($data);
    }

    /**
     * Delete a message.
     *
     * @param int $messageId
     * @return bool
     */
    public function deleteMessage(int $messageId): bool
    {
        $message = $this->getMessageById($messageId);
        
        if (!$message) {
            return false;
        }
        
        return $message->delete();
    }

    /**
     * Get all announcements for a group.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupAnnouncements(int $groupId, int $perPage = 15): LengthAwarePaginator
    {
        return GroupMessage::where('group_id', $groupId)
            ->where('is_announcement', true)
            ->with(['sender'])
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all pinned messages for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getPinnedMessages(int $groupId): Collection
    {
        $cacheKey = "group_{$groupId}_pinned_messages";
        
        return Cache::remember($cacheKey, 60 * 5, function() use ($groupId) {
            return GroupMessage::where('group_id', $groupId)
                ->where('is_pinned', true)
                ->with(['sender'])
                ->where(function($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get unread messages count for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return int
     */
    public function getUnreadMessagesCount(int $groupId, int $memberId): int
    {
        $cacheKey = "group_{$groupId}_member_{$memberId}_unread_count";
        
        return Cache::remember($cacheKey, 60, function() use ($groupId, $memberId) {
            return GroupMessageRecipient::whereHas('message', function($query) use ($groupId) {
                $query->where('group_id', $groupId)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->where('member_id', $memberId)
            ->where('is_read', false)
            ->count();
        });
    }

    /**
     * Mark a message as read by a member.
     *
     * @param int $messageId
     * @param int $memberId
     * @return bool
     */
    public function markMessageAsRead(int $messageId, int $memberId): bool
    {
        $recipient = GroupMessageRecipient::where('group_message_id', $messageId)
            ->where('member_id', $memberId)
            ->first();
        
        if (!$recipient) {
            return false;
        }
        
        $result = $recipient->markAsRead();
        
        // Clear cache for unread count
        if ($result) {
            $message = GroupMessage::find($messageId);
            if ($message) {
                $cacheKey = "group_{$message->group_id}_member_{$memberId}_unread_count";
                Cache::forget($cacheKey);
            }
        }
        
        return $result;
    }

    /**
     * Mark all messages as read for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return bool
     */
    public function markAllMessagesAsRead(int $groupId, int $memberId): bool
    {
        try {
            // Get all unread message recipients for this member in this group
            $recipients = GroupMessageRecipient::whereHas('message', function($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->where('member_id', $memberId)
            ->where('is_read', false)
            ->get();
            
            // Mark each as read
            foreach ($recipients as $recipient) {
                $recipient->markAsRead();
            }
            
            // Clear cache for unread count
            $cacheKey = "group_{$groupId}_member_{$memberId}_unread_count";
            Cache::forget($cacheKey);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add recipients to a message.
     *
     * @param int $messageId
     * @param array $memberIds
     * @return bool
     */
    public function addMessageRecipients(int $messageId, array $memberIds): bool
    {
        try {
            $recipients = [];
            $now = now();
            
            foreach ($memberIds as $memberId) {
                $recipients[] = [
                    'group_message_id' => $messageId,
                    'member_id' => $memberId,
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            
            // Insert all recipients at once
            GroupMessageRecipient::insert($recipients);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
