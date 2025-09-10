<?php

namespace App\Services\Interfaces;

use App\Models\EventShare;
use Illuminate\Database\Eloquent\Collection;

interface EventShareServiceInterface
{
    /**
     * Get all shares for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventShares(int $eventId): Collection;
    
    /**
     * Get a specific share by ID.
     *
     * @param int $shareId
     * @return EventShare|null
     */
    public function getShareById(int $shareId): ?EventShare;
    
    /**
     * Get a share by token.
     *
     * @param string $token
     * @return EventShare|null
     */
    public function getShareByToken(string $token): ?EventShare;
    
    /**
     * Create a new share for an event.
     *
     * @param array $data
     * @return EventShare
     */
    public function createShare(array $data): EventShare;
    
    /**
     * Update an existing share.
     *
     * @param int $shareId
     * @param array $data
     * @return EventShare|null
     */
    public function updateShare(int $shareId, array $data): ?EventShare;
    
    /**
     * Delete a share.
     *
     * @param int $shareId
     * @return bool
     */
    public function deleteShare(int $shareId): bool;
    
    /**
     * Get shares by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @return Collection
     */
    public function getSharesByStatus(int $eventId, string $status): Collection;
    
    /**
     * Get shares for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getSharesForGroup(int $groupId): Collection;
    
    /**
     * Get shares for a member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSharesForMember(int $memberId): Collection;
    
    /**
     * Accept a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function acceptShare(string $token): bool;
    
    /**
     * Decline a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function declineShare(string $token): bool;
    
    /**
     * Send share invitation notification.
     *
     * @param EventShare $share
     * @return bool
     */
    public function sendShareInvitation(EventShare $share): bool;
}
