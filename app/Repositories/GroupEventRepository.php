<?php

namespace App\Repositories;

use App\Models\GroupEvent;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class GroupEventRepository implements GroupEventRepositoryInterface
{
    /**
     * Get all group events with pagination.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllGroupEvents(int $groupId, int $perPage = 10): LengthAwarePaginator
    {
        return GroupEvent::where('group_id', $groupId)
            ->orderBy('event_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Get a specific group event by ID.
     *
     * @param int $eventId
     * @return GroupEvent|null
     */
    public function getGroupEventById(int $eventId): ?GroupEvent
    {
        return Cache::remember("group_event_{$eventId}", 60 * 5, function () use ($eventId) {
            return GroupEvent::with(['group', 'creator'])->find($eventId);
        });
    }
    
    /**
     * Create a new group event.
     *
     * @param array $data
     * @return GroupEvent
     */
    public function createGroupEvent(array $data): GroupEvent
    {
        $event = GroupEvent::create($data);
        
        // Clear any relevant cache
        $this->clearGroupEventsCache($event->group_id);
        
        return $event;
    }
    
    /**
     * Update an existing group event.
     *
     * @param int $eventId
     * @param array $data
     * @return GroupEvent|null
     */
    public function updateGroupEvent(int $eventId, array $data): ?GroupEvent
    {
        $event = GroupEvent::find($eventId);
        
        if (!$event) {
            return null;
        }
        
        $event->update($data);
        
        // Clear any relevant cache
        Cache::forget("group_event_{$eventId}");
        $this->clearGroupEventsCache($event->group_id);
        
        return $event->fresh();
    }
    
    /**
     * Delete a group event.
     *
     * @param int $eventId
     * @return bool
     */
    public function deleteGroupEvent(int $eventId): bool
    {
        $event = GroupEvent::find($eventId);
        
        if (!$event) {
            return false;
        }
        
        $groupId = $event->group_id;
        $result = $event->delete();
        
        // Clear any relevant cache
        Cache::forget("group_event_{$eventId}");
        $this->clearGroupEventsCache($groupId);
        
        return $result;
    }
    
    /**
     * Get upcoming events for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return Collection
     */
    public function getUpcomingEvents(int $groupId, int $limit = 5): Collection
    {
        return Cache::remember("group_{$groupId}_upcoming_events_{$limit}", 60 * 5, function () use ($groupId, $limit) {
            return GroupEvent::where('group_id', $groupId)
                ->where('event_date', '>=', now()->format('Y-m-d'))
                ->where('is_active', true)
                ->orderBy('event_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->limit($limit)
                ->get();
        });
    }
    
    /**
     * Get past events for a group.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPastEvents(int $groupId, int $perPage = 10): LengthAwarePaginator
    {
        return GroupEvent::where('group_id', $groupId)
            ->where('event_date', '<', now()->format('Y-m-d'))
            ->orderBy('event_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Get events by type.
     *
     * @param int $groupId
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventsByType(int $groupId, string $type, int $perPage = 10): LengthAwarePaginator
    {
        return GroupEvent::where('group_id', $groupId)
            ->where('event_type', $type)
            ->orderBy('event_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Get events for a specific date range.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventsByDateRange(int $groupId, string $startDate, string $endDate, int $perPage = 10): LengthAwarePaginator
    {
        return GroupEvent::where('group_id', $groupId)
            ->whereBetween('event_date', [$startDate, $endDate])
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Clear all group events related cache.
     *
     * @param int $groupId
     * @return void
     */
    private function clearGroupEventsCache(int $groupId): void
    {
        Cache::forget("group_{$groupId}_upcoming_events_5");
        Cache::forget("group_{$groupId}_upcoming_events_10");
        Cache::forget("group_{$groupId}_events");
    }
}
