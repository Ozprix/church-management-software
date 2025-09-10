<?php

namespace App\Services;

use App\Models\GroupEvent;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Services\Interfaces\GroupEventServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GroupEventService implements GroupEventServiceInterface
{
    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * GroupEventService constructor.
     *
     * @param GroupEventRepositoryInterface $groupEventRepository
     */
    public function __construct(GroupEventRepositoryInterface $groupEventRepository)
    {
        $this->groupEventRepository = $groupEventRepository;
    }

    /**
     * Get all group events with pagination.
     *
     * @param int $groupId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllGroupEvents(int $groupId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->groupEventRepository->getAllGroupEvents($groupId, $perPage);
    }
    
    /**
     * Get a specific group event by ID.
     *
     * @param int $eventId
     * @return GroupEvent|null
     */
    public function getGroupEventById(int $eventId): ?GroupEvent
    {
        return $this->groupEventRepository->getGroupEventById($eventId);
    }
    
    /**
     * Create a new group event.
     *
     * @param array $data
     * @return GroupEvent
     */
    public function createGroupEvent(array $data): GroupEvent
    {
        // Add any business logic here before creating the event
        
        // If this is a recurring event, we might want to handle recurrence pattern validation
        if (isset($data['is_recurring']) && $data['is_recurring']) {
            $this->validateRecurrenceData($data);
        }
        
        return $this->groupEventRepository->createGroupEvent($data);
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
        // Add any business logic here before updating the event
        
        // If this is a recurring event, we might want to handle recurrence pattern validation
        if (isset($data['is_recurring']) && $data['is_recurring']) {
            $this->validateRecurrenceData($data);
        }
        
        return $this->groupEventRepository->updateGroupEvent($eventId, $data);
    }
    
    /**
     * Delete a group event.
     *
     * @param int $eventId
     * @return bool
     */
    public function deleteGroupEvent(int $eventId): bool
    {
        return $this->groupEventRepository->deleteGroupEvent($eventId);
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
        return $this->groupEventRepository->getUpcomingEvents($groupId, $limit);
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
        return $this->groupEventRepository->getPastEvents($groupId, $perPage);
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
        return $this->groupEventRepository->getEventsByType($groupId, $type, $perPage);
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
        return $this->groupEventRepository->getEventsByDateRange($groupId, $startDate, $endDate, $perPage);
    }
    
    /**
     * Validate recurrence data.
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateRecurrenceData(array $data): void
    {
        // Check if required recurrence fields are present
        if (!isset($data['recurrence_pattern'])) {
            throw new \InvalidArgumentException('Recurrence pattern is required for recurring events.');
        }
        
        // Validate recurrence pattern
        $validPatterns = ['daily', 'weekly', 'monthly', 'yearly'];
        if (!in_array($data['recurrence_pattern'], $validPatterns)) {
            throw new \InvalidArgumentException('Invalid recurrence pattern. Must be one of: ' . implode(', ', $validPatterns));
        }
        
        // For weekly and monthly patterns, we need the recurrence day
        if (in_array($data['recurrence_pattern'], ['weekly', 'monthly']) && !isset($data['recurrence_day'])) {
            throw new \InvalidArgumentException('Recurrence day is required for weekly and monthly recurring events.');
        }
    }
}
