<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupMessageRepositoryInterface
{
    /**
     * Get all messages for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupMessages(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a message by ID.
     *
     * @param int $messageId
     * @return GroupMessage|null
     */
    public function getMessageById(int $messageId): ?GroupMessage;

    /**
     * Create a new message.
     *
     * @param array $data
     * @return GroupMessage
     */
    public function createMessage(array $data): GroupMessage;

    /**
     * Update a message.
     *
     * @param int $messageId
     * @param array $data
     * @return bool
     */
    public function updateMessage(int $messageId, array $data): bool;

    /**
     * Delete a message.
     *
     * @param int $messageId
     * @return bool
     */
    public function deleteMessage(int $messageId): bool;

    /**
     * Get all announcements for a group.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupAnnouncements(int $groupId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all pinned messages for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getPinnedMessages(int $groupId): Collection;

    /**
     * Get unread messages count for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return int
     */
    public function getUnreadMessagesCount(int $groupId, int $memberId): int;

    /**
     * Mark a message as read by a member.
     *
     * @param int $messageId
     * @param int $memberId
     * @return bool
     */
    public function markMessageAsRead(int $messageId, int $memberId): bool;

    /**
     * Mark all messages as read for a member in a group.
     *
     * @param int $groupId
     * @param int $memberId
     * @return bool
     */
    public function markAllMessagesAsRead(int $groupId, int $memberId): bool;

    /**
     * Add recipients to a message.
     *
     * @param int $messageId
     * @param array $memberIds
     * @return bool
     */
    public function addMessageRecipients(int $messageId, array $memberIds): bool;
}
