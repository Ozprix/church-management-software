<?php

namespace App\Repositories;

use App\Models\GroupPrayerRequest;
use App\Models\GroupPrayerResponse;
use App\Repositories\Interfaces\GroupPrayerRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GroupPrayerRequestRepository implements GroupPrayerRequestRepositoryInterface
{
    /**
     * Get all prayer requests for a group.
     *
     * @param int $groupId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGroupPrayerRequests(int $groupId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = GroupPrayerRequest::where('group_id', $groupId)
            ->with(['member', 'responses'])
            ->withCount(['responses as praying_count' => function($query) {
                $query->where('is_praying', true);
            }]);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Default to active requests
            $query->where('status', 'active');
        }
        
        if (isset($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }
        
        if (isset($filters['is_anonymous'])) {
            $query->where('is_anonymous', $filters['is_anonymous']);
        }
        
        if (isset($filters['is_private'])) {
            $query->where('is_private', $filters['is_private']);
        } else {
            // By default, only show public requests unless specifically requested
            $query->where('is_private', false);
        }
        
        if (isset($filters['search'])) {
            $query->where(function($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Order by
        if (isset($filters['order_by'])) {
            $direction = $filters['order_direction'] ?? 'desc';
            $query->orderBy($filters['order_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Get a prayer request by ID.
     *
     * @param int $requestId
     * @return GroupPrayerRequest|null
     */
    public function getPrayerRequestById(int $requestId): ?GroupPrayerRequest
    {
        return GroupPrayerRequest::with(['member', 'responses.member'])
            ->withCount(['responses as praying_count' => function($query) {
                $query->where('is_praying', true);
            }])
            ->find($requestId);
    }

    /**
     * Create a new prayer request.
     *
     * @param array $data
     * @return GroupPrayerRequest
     */
    public function createPrayerRequest(array $data): GroupPrayerRequest
    {
        return GroupPrayerRequest::create([
            'group_id' => $data['group_id'],
            'member_id' => $data['member_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'active',
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_private' => $data['is_private'] ?? false,
        ]);
    }

    /**
     * Update a prayer request.
     *
     * @param int $requestId
     * @param array $data
     * @return bool
     */
    public function updatePrayerRequest(int $requestId, array $data): bool
    {
        $request = $this->getPrayerRequestById($requestId);
        
        if (!$request) {
            return false;
        }
        
        return $request->update($data);
    }

    /**
     * Delete a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function deletePrayerRequest(int $requestId): bool
    {
        $request = $this->getPrayerRequestById($requestId);
        
        if (!$request) {
            return false;
        }
        
        return $request->delete();
    }

    /**
     * Mark a prayer request as answered.
     *
     * @param int $requestId
     * @param string|null $answerDescription
     * @return bool
     */
    public function markAsAnswered(int $requestId, ?string $answerDescription = null): bool
    {
        $request = $this->getPrayerRequestById($requestId);
        
        if (!$request) {
            return false;
        }
        
        return $request->markAsAnswered($answerDescription);
    }

    /**
     * Archive a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function archivePrayerRequest(int $requestId): bool
    {
        $request = $this->getPrayerRequestById($requestId);
        
        if (!$request) {
            return false;
        }
        
        return $request->archive();
    }

    /**
     * Reactivate a prayer request.
     *
     * @param int $requestId
     * @return bool
     */
    public function reactivatePrayerRequest(int $requestId): bool
    {
        $request = $this->getPrayerRequestById($requestId);
        
        if (!$request) {
            return false;
        }
        
        return $request->reactivate();
    }

    /**
     * Add a prayer response to a prayer request.
     *
     * @param int $requestId
     * @param int $memberId
     * @param string $response
     * @param bool $isPraying
     * @return bool
     */
    public function addPrayerResponse(int $requestId, int $memberId, string $response, bool $isPraying = true): bool
    {
        try {
            // Check if the prayer request exists
            $request = $this->getPrayerRequestById($requestId);
            
            if (!$request) {
                return false;
            }
            
            // Check if the member has already responded
            $existingResponse = GroupPrayerResponse::where('prayer_request_id', $requestId)
                ->where('member_id', $memberId)
                ->first();
            
            if ($existingResponse) {
                // Update existing response
                $existingResponse->response = $response;
                $existingResponse->is_praying = $isPraying;
                return $existingResponse->save();
            } else {
                // Create new response
                GroupPrayerResponse::create([
                    'prayer_request_id' => $requestId,
                    'member_id' => $memberId,
                    'response' => $response,
                    'is_praying' => $isPraying,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Toggle the praying status for a member on a prayer request.
     *
     * @param int $requestId
     * @param int $memberId
     * @return bool
     */
    public function togglePraying(int $requestId, int $memberId): bool
    {
        try {
            // Check if the prayer request exists
            $request = $this->getPrayerRequestById($requestId);
            
            if (!$request) {
                return false;
            }
            
            // Check if the member has already responded
            $existingResponse = GroupPrayerResponse::where('prayer_request_id', $requestId)
                ->where('member_id', $memberId)
                ->first();
            
            if ($existingResponse) {
                // Toggle praying status
                return $existingResponse->togglePraying();
            } else {
                // Create new response with empty text but praying status
                GroupPrayerResponse::create([
                    'prayer_request_id' => $requestId,
                    'member_id' => $memberId,
                    'response' => '',
                    'is_praying' => true,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get prayer requests statistics for a group.
     *
     * @param int $groupId
     * @return array
     */
    public function getPrayerRequestsStats(int $groupId): array
    {
        $cacheKey = "group_{$groupId}_prayer_request_stats";
        
        return Cache::remember($cacheKey, 60 * 15, function() use ($groupId) {
            $total = GroupPrayerRequest::where('group_id', $groupId)->count();
            $active = GroupPrayerRequest::where('group_id', $groupId)->where('status', 'active')->count();
            $answered = GroupPrayerRequest::where('group_id', $groupId)->where('status', 'answered')->count();
            $archived = GroupPrayerRequest::where('group_id', $groupId)->where('status', 'archived')->count();
            
            // Get total prayers (sum of all praying responses)
            $totalPrayers = GroupPrayerResponse::whereHas('prayerRequest', function($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })->where('is_praying', true)->count();
            
            // Get recent prayer requests (last 30 days)
            $recentRequests = GroupPrayerRequest::where('group_id', $groupId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            
            // Get recent answered prayers (last 30 days)
            $recentAnswered = GroupPrayerRequest::where('group_id', $groupId)
                ->where('status', 'answered')
                ->where('answered_at', '>=', now()->subDays(30))
                ->count();
            
            return [
                'total' => $total,
                'active' => $active,
                'answered' => $answered,
                'archived' => $archived,
                'total_prayers' => $totalPrayers,
                'recent_requests' => $recentRequests,
                'recent_answered' => $recentAnswered,
                'answer_rate' => $total > 0 ? round(($answered / $total) * 100, 2) : 0,
            ];
        });
    }
}
