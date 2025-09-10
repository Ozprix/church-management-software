<?php

namespace App\Repositories\Interfaces;

use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventRegistrationRepositoryInterface
{
    /**
     * Get all registrations for an event.
     *
     * @param int $eventId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getEventRegistrations(int $eventId, int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Get a specific registration by ID.
     *
     * @param int $registrationId
     * @return EventRegistration|null
     */
    public function getRegistrationById(int $registrationId): ?EventRegistration;
    
    /**
     * Create a new registration for an event.
     *
     * @param array $data
     * @return EventRegistration
     */
    public function createRegistration(array $data): EventRegistration;
    
    /**
     * Update an existing registration.
     *
     * @param int $registrationId
     * @param array $data
     * @return EventRegistration|null
     */
    public function updateRegistration(int $registrationId, array $data): ?EventRegistration;
    
    /**
     * Delete a registration.
     *
     * @param int $registrationId
     * @return bool
     */
    public function deleteRegistration(int $registrationId): bool;
    
    /**
     * Get registrations for a member across all events.
     *
     * @param int $memberId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMemberRegistrations(int $memberId, int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Get registration for a specific member and event.
     *
     * @param int $eventId
     * @param int $memberId
     * @return EventRegistration|null
     */
    public function getMemberEventRegistration(int $eventId, int $memberId): ?EventRegistration;
    
    /**
     * Get registrations by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRegistrationsByStatus(int $eventId, string $status, int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Count registrations for an event.
     *
     * @param int $eventId
     * @return int
     */
    public function countEventRegistrations(int $eventId): int;
    
    /**
     * Count registrations by status for an event.
     *
     * @param int $eventId
     * @param string $status
     * @return int
     */
    public function countRegistrationsByStatus(int $eventId, string $status): int;
}
