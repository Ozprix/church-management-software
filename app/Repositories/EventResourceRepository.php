<?php

namespace App\Repositories;

use App\Models\EventResource;
use App\Repositories\Interfaces\EventResourceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EventResourceRepository implements EventResourceRepositoryInterface
{
    /**
     * Get all resources for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventResources(int $eventId): Collection
    {
        return Cache::remember("event_{$eventId}_resources", 60 * 5, function () use ($eventId) {
            return EventResource::where('group_event_id', $eventId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Get a specific resource by ID.
     *
     * @param int $resourceId
     * @return EventResource|null
     */
    public function getResourceById(int $resourceId): ?EventResource
    {
        return Cache::remember("event_resource_{$resourceId}", 60 * 5, function () use ($resourceId) {
            return EventResource::with('event', 'uploader')->find($resourceId);
        });
    }
    
    /**
     * Create a new resource for an event.
     *
     * @param array $data
     * @return EventResource
     */
    public function createResource(array $data): EventResource
    {
        $resource = EventResource::create($data);
        
        // Clear cache
        $this->clearResourceCache($resource->group_event_id);
        
        return $resource;
    }
    
    /**
     * Update an existing resource.
     *
     * @param int $resourceId
     * @param array $data
     * @return EventResource|null
     */
    public function updateResource(int $resourceId, array $data): ?EventResource
    {
        $resource = EventResource::find($resourceId);
        
        if (!$resource) {
            return null;
        }
        
        $resource->update($data);
        
        // Clear cache
        Cache::forget("event_resource_{$resourceId}");
        $this->clearResourceCache($resource->group_event_id);
        
        return $resource->fresh();
    }
    
    /**
     * Delete a resource.
     *
     * @param int $resourceId
     * @return bool
     */
    public function deleteResource(int $resourceId): bool
    {
        $resource = EventResource::find($resourceId);
        
        if (!$resource) {
            return false;
        }
        
        $eventId = $resource->group_event_id;
        
        // Delete the file if it exists
        if ($resource->file_path && Storage::exists($resource->file_path)) {
            Storage::delete($resource->file_path);
        }
        
        $result = $resource->delete();
        
        // Clear cache
        Cache::forget("event_resource_{$resourceId}");
        $this->clearResourceCache($eventId);
        
        return $result;
    }
    
    /**
     * Get public resources for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getPublicResources(int $eventId): Collection
    {
        return Cache::remember("event_{$eventId}_public_resources", 60 * 5, function () use ($eventId) {
            return EventResource::where('group_event_id', $eventId)
                ->where('is_public', true)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Get resources by type for an event.
     *
     * @param int $eventId
     * @param string $type
     * @return Collection
     */
    public function getResourcesByType(int $eventId, string $type): Collection
    {
        return Cache::remember("event_{$eventId}_resources_type_{$type}", 60 * 5, function () use ($eventId, $type) {
            return EventResource::where('group_event_id', $eventId)
                ->where('resource_type', $type)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Clear all resource-related cache for an event.
     *
     * @param int $eventId
     * @return void
     */
    private function clearResourceCache(int $eventId): void
    {
        Cache::forget("event_{$eventId}_resources");
        Cache::forget("event_{$eventId}_public_resources");
        
        // Clear type-specific caches
        $types = ['document', 'video', 'audio', 'link'];
        foreach ($types as $type) {
            Cache::forget("event_{$eventId}_resources_type_{$type}");
        }
    }
}
