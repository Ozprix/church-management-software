<?php

namespace App\Repositories;

use App\Models\EventShare;
use App\Repositories\Interfaces\EventShareRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EventShareRepository implements EventShareRepositoryInterface
{
    /**
     * Get all shares for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventShares(int $eventId): Collection
    {
        return Cache::remember("event_{$eventId}_shares", 60 * 5, function () use ($eventId) {
            return EventShare::with(['sharedBy', 'sharedWithGroup', 'sharedWithMember'])
                ->where('group_event_id', $eventId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Get a specific share by ID.
     *
     * @param int $shareId
     * @return EventShare|null
     */
    public function getShareById(int $shareId): ?EventShare
    {
        return Cache::remember("event_share_{$shareId}", 60 * 5, function () use ($shareId) {
            return EventShare::with(['event', 'sharedBy', 'sharedWithGroup', 'sharedWithMember'])
                ->find($shareId);
        });
    }
    
    /**
     * Get a share by token.
     *
     * @param string $token
     * @return EventShare|null
     */
    public function getShareByToken(string $token): ?EventShare
    {
        return Cache::remember("event_share_token_{$token}", 60 * 5, function () use ($token) {
            return EventShare::with(['event', 'sharedBy', 'sharedWithGroup', 'sharedWithMember'])
                ->where('token', $token)
                ->first();
        });
    }
    
    /**
     * Create a new share for an event.
     *
     * @param array $data
     * @return EventShare
     */
    public function createShare(array $data): EventShare
    {
        $share = EventShare::create($data);
        
        // Clear cache
        $this->clearShareCache($share->group_event_id, $share->shared_with_group_id, $share->shared_with_member_id);
        
        return $share;
    }
    
    /**
     * Update an existing share.
     *
     * @param int $shareId
     * @param array $data
     * @return EventShare|null
     */
    public function updateShare(int $shareId, array $data): ?EventShare
    {
        $share = EventShare::find($shareId);
        
        if (!$share) {
            return null;
        }
        
        $share->update($data);
        
        // Clear cache
        Cache::forget("event_share_{$shareId}");
        if ($share->token) {
            Cache::forget("event_share_token_{$share->token}");
        }
        $this->clearShareCache($share->group_event_id, $share->shared_with_group_id, $share->shared_with_member_id);
        
        return $share->fresh();
    }
    
    /**
     * Delete a share.
     *
     * @param int $shareId
     * @return bool
     */
    public function deleteShare(int $shareId): bool
    {
        $share = EventShare::find($shareId);
        
        if (!$share) {
            return false;
        }
        
        $eventId = $share->group_event_id;
        $groupId = $share->shared_with_group_id;
        $memberId = $share->shared_with_member_id;
        $token = $share->token;
        
        $result = $share->delete();
        
        // Clear cache
        Cache::forget("event_share_{$shareId}");
        if ($token) {
            Cache::forget("event_share_token_{$token}");
        }
        $this->clearShareCache($eventId, $groupId, $memberId);
        
        return $result;
    }
    
    /**
     * Get shares by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @return Collection
     */
    public function getSharesByStatus(int $eventId, string $status): Collection
    {
        return Cache::remember("event_{$eventId}_shares_status_{$status}", 60 * 5, function () use ($eventId, $status) {
            return EventShare::with(['sharedBy', 'sharedWithGroup', 'sharedWithMember'])
                ->where('group_event_id', $eventId)
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Get shares for a group.
     *
     * @param int $groupId
     * @return Collection
     */
    public function getSharesForGroup(int $groupId): Collection
    {
        return Cache::remember("group_{$groupId}_event_shares", 60 * 5, function () use ($groupId) {
            return EventShare::with(['event', 'sharedBy'])
                ->where('shared_with_group_id', $groupId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Get shares for a member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSharesForMember(int $memberId): Collection
    {
        return Cache::remember("member_{$memberId}_event_shares", 60 * 5, function () use ($memberId) {
            return EventShare::with(['event', 'sharedBy'])
                ->where('shared_with_member_id', $memberId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Accept a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function acceptShare(string $token): bool
    {
        $share = $this->getShareByToken($token);
        
        if (!$share || !$share->is_valid) {
            return false;
        }
        
        $result = $share->accept();
        
        // Clear cache
        Cache::forget("event_share_token_{$token}");
        Cache::forget("event_share_{$share->id}");
        $this->clearShareCache($share->group_event_id, $share->shared_with_group_id, $share->shared_with_member_id);
        
        return $result;
    }
    
    /**
     * Decline a share invitation.
     *
     * @param string $token
     * @return bool
     */
    public function declineShare(string $token): bool
    {
        $share = $this->getShareByToken($token);
        
        if (!$share || !$share->is_valid) {
            return false;
        }
        
        $result = $share->decline();
        
        // Clear cache
        Cache::forget("event_share_token_{$token}");
        Cache::forget("event_share_{$share->id}");
        $this->clearShareCache($share->group_event_id, $share->shared_with_group_id, $share->shared_with_member_id);
        
        return $result;
    }
    
    /**
     * Clear all share-related cache for an event, group, and member.
     *
     * @param int|null $eventId
     * @param int|null $groupId
     * @param int|null $memberId
     * @return void
     */
    private function clearShareCache(?int $eventId, ?int $groupId, ?int $memberId): void
    {
        if ($eventId) {
            Cache::forget("event_{$eventId}_shares");
            
            // Clear status-specific caches
            $statuses = ['pending', 'accepted', 'declined'];
            foreach ($statuses as $status) {
                Cache::forget("event_{$eventId}_shares_status_{$status}");
            }
        }
        
        if ($groupId) {
            Cache::forget("group_{$groupId}_event_shares");
        }
        
        if ($memberId) {
            Cache::forget("member_{$memberId}_event_shares");
        }
    }
}
