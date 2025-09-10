<?php

namespace App\Repositories\Interfaces;

use App\Models\GroupEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GroupEventRepositoryInterface
{
    /**
     * Get all group events with pagination.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllGroupEvents(int $groupId, int $perPage = 10): LengthAwarePaginator;
    
    /**
     * Get a specific group event by ID.
     *
     * @param int $eventId
     * @return GroupEvent|null
     */
    public function getGroupEventById(int $eventId): ?GroupEvent;
    
    /**
     * Create a new group event.
     *
     * @param array $data
     * @return GroupEvent
     */
    public function createGroupEvent(array $data): GroupEvent;
    
    /**
     * Update an existing group event.
     *
     * @param int $eventId
     * @param array $data
     * @return GroupEvent|null
     */
    public function updateGroupEvent(int $eventId, array $data): ?GroupEvent;
    
    /**
     * Delete a group event.
     *
     * @param int $eventId
     * @return bool
     */
    public function deleteGroupEvent(int $eventId): bool;
    
    /**
     * Get upcoming events for a group.
     *
     * @param int $groupId
     * @param int $limit
     * @return Collection
     */
    public function getUpcomingEvents(int $groupId, int $limit = 5): Collection;
    
    /**
     * Get past events for a group.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPastEvents(int $groupId, int $perPage = 10): LengthAwarePaginator;
    
    /**
     * Get events by type.
     *
     * @param int $groupId
     * @param string $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventsByType(int $groupId, string $type, int $perPage = 10): LengthAwarePaginator;
    
    /**
     * Get events for a specific date range.
     *
     * @param int $groupId
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventsByDateRange(int $groupId, string $startDate, string $endDate, int $perPage = 10): LengthAwarePaginator;
}
