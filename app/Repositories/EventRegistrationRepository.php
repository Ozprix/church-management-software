<?php

namespace App\Repositories;

use App\Models\EventRegistration;
use App\Models\GroupEvent;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class EventRegistrationRepository implements EventRegistrationRepositoryInterface
{
    /**
     * Get all registrations for an event.
     *
     * @param int $eventId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventRegistrations(int $eventId, int $perPage = 15): LengthAwarePaginator
    {
        return EventRegistration::with('member')
            ->where('group_event_id', $eventId)
            ->orderBy('registered_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Get a specific registration by ID.
     *
     * @param int $registrationId
     * @return EventRegistration|null
     */
    public function getRegistrationById(int $registrationId): ?EventRegistration
    {
        return Cache::remember("event_registration_{$registrationId}", 60 * 5, function () use ($registrationId) {
            return EventRegistration::with(['event', 'member'])->find($registrationId);
        });
    }
    
    /**
     * Create a new registration for an event.
     *
     * @param array $data
     * @return EventRegistration
     */
    public function createRegistration(array $data): EventRegistration
    {
        $registration = EventRegistration::create($data);
        
        // Update event registration count
        $event = GroupEvent::find($data['group_event_id']);
        if ($event) {
            $event->incrementRegistrationCount();
        }
        
        // Clear cache
        $this->clearRegistrationCache($registration->group_event_id, $registration->member_id);
        
        return $registration;
    }
    
    /**
     * Update an existing registration.
     *
     * @param int $registrationId
     * @param array $data
     * @return EventRegistration|null
     */
    public function updateRegistration(int $registrationId, array $data): ?EventRegistration
    {
        $registration = EventRegistration::find($registrationId);
        
        if (!$registration) {
            return null;
        }
        
        $registration->update($data);
        
        // Clear cache
        Cache::forget("event_registration_{$registrationId}");
        $this->clearRegistrationCache($registration->group_event_id, $registration->member_id);
        
        return $registration->fresh();
    }
    
    /**
     * Delete a registration.
     *
     * @param int $registrationId
     * @return bool
     */
    public function deleteRegistration(int $registrationId): bool
    {
        $registration = EventRegistration::find($registrationId);
        
        if (!$registration) {
            return false;
        }
        
        $eventId = $registration->group_event_id;
        $memberId = $registration->member_id;
        
        $result = $registration->delete();
        
        // Update event registration count
        $event = GroupEvent::find($eventId);
        if ($event) {
            $event->decrementRegistrationCount();
        }
        
        // Clear cache
        Cache::forget("event_registration_{$registrationId}");
        $this->clearRegistrationCache($eventId, $memberId);
        
        return $result;
    }
    
    /**
     * Get registrations for a member across all events.
     *
     * @param int $memberId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMemberRegistrations(int $memberId, int $perPage = 15): LengthAwarePaginator
    {
        return EventRegistration::with('event')
            ->where('member_id', $memberId)
            ->orderBy('registered_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Get registration for a specific member and event.
     *
     * @param int $eventId
     * @param int $memberId
     * @return EventRegistration|null
     */
    public function getMemberEventRegistration(int $eventId, int $memberId): ?EventRegistration
    {
        return Cache::remember("event_{$eventId}_member_{$memberId}_registration", 60 * 5, function () use ($eventId, $memberId) {
            return EventRegistration::where('group_event_id', $eventId)
                ->where('member_id', $memberId)
                ->first();
        });
    }
    
    /**
     * Get registrations by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRegistrationsByStatus(int $eventId, string $status, int $perPage = 15): LengthAwarePaginator
    {
        return EventRegistration::with('member')
            ->where('group_event_id', $eventId)
            ->where('status', $status)
            ->orderBy('registered_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Count registrations for an event.
     *
     * @param int $eventId
     * @return int
     */
    public function countEventRegistrations(int $eventId): int
    {
        return Cache::remember("event_{$eventId}_registration_count", 60 * 5, function () use ($eventId) {
            return EventRegistration::where('group_event_id', $eventId)->count();
        });
    }
    
    /**
     * Count registrations by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @return int
     */
    public function countRegistrationsByStatus(int $eventId, string $status): int
    {
        return Cache::remember("event_{$eventId}_registration_status_{$status}_count", 60 * 5, function () use ($eventId, $status) {
            return EventRegistration::where('group_event_id', $eventId)
                ->where('status', $status)
                ->count();
        });
    }
    
    /**
     * Clear all registration-related cache for an event and member.
     *
     * @param int $eventId
     * @param int|null $memberId
     * @return void
     */
    private function clearRegistrationCache(int $eventId, ?int $memberId): void
    {
        Cache::forget("event_{$eventId}_registrations");
        Cache::forget("event_{$eventId}_registration_count");
        
        // Clear status-specific caches
        $statuses = ['registered', 'confirmed', 'attended', 'cancelled'];
        foreach ($statuses as $status) {
            Cache::forget("event_{$eventId}_registration_status_{$status}_count");
        }
        
        // Clear member-specific cache if applicable
        if ($memberId) {
            Cache::forget("event_{$eventId}_member_{$memberId}_registration");
            Cache::forget("member_{$memberId}_registrations");
        }
    }
}
