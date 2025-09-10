<?php

namespace App\Repositories\Interfaces;

use App\Models\EventResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventResourceRepositoryInterface
{
    /**
     * Get all resources for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventResources(int $eventId): Collection;
    
    /**
     * Get a specific resource by ID.
     *
     * @param int $resourceId
     * @return EventResource|null
     */
    public function getResourceById(int $resourceId): ?EventResource;
    
    /**
     * Create a new resource for an event.
     *
     * @param array $data
     * @return EventResource
     */
    public function createResource(array $data): EventResource;
    
    /**
     * Update an existing resource.
     *
     * @param int $resourceId
     * @param array $data
     * @return EventResource|null
     */
    public function updateResource(int $resourceId, array $data): ?EventResource;
    
    /**
     * Delete a resource.
     *
     * @param int $resourceId
     * @return bool
     */
    public function deleteResource(int $resourceId): bool;
    
    /**
     * Get public resources for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getPublicResources(int $eventId): Collection;
    
    /**
     * Get resources by type for an event.
     *
     * @param int $eventId
     * @param string $type
     * @return Collection
     */
    public function getResourcesByType(int $eventId, string $type): Collection;
}
