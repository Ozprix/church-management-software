<?php

namespace App\Repositories;

use App\Models\MemberAvailability;
use App\Models\Member;
use App\Repositories\Interfaces\MemberAvailabilityRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class MemberAvailabilityRepository implements MemberAvailabilityRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all availability records.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllAvailability(array $filters = []): Collection
    {
        $cacheKey = CacheService::generateKey('availability', 'all', ['filters' => $filters]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($filters) {
            $query = MemberAvailability::with('member');
            
            if (isset($filters['day_of_week'])) {
                $query->where('day_of_week', $filters['day_of_week']);
            }
            
            if (isset($filters['is_recurring'])) {
                $query->where('is_recurring', $filters['is_recurring']);
            }
            
            if (isset($filters['current_only']) && $filters['current_only']) {
                $query->current();
            }
            
            return $query->orderBy('day_of_week')->orderBy('start_time')->get();
        });
    }

    /**
     * Get paginated availability records.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedAvailability(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('availability', 'paginated', [
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = MemberAvailability::with('member');
            
            if (isset($filters['day_of_week'])) {
                $query->where('day_of_week', $filters['day_of_week']);
            }
            
            if (isset($filters['is_recurring'])) {
                $query->where('is_recurring', $filters['is_recurring']);
            }
            
            if (isset($filters['current_only']) && $filters['current_only']) {
                $query->current();
            }
            
            if (isset($filters['member_id'])) {
                $query->where('member_id', $filters['member_id']);
            }
            
            return $query->orderBy('day_of_week')->orderBy('start_time')->paginate($perPage);
        });
    }

    /**
     * Get availability record by ID.
     *
     * @param int $id
     * @return MemberAvailability|null
     */
    public function getAvailabilityById(int $id): ?MemberAvailability
    {
        $cacheKey = CacheService::generateKey('availability', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return MemberAvailability::with('member')->find($id);
        });
    }

    /**
     * Create a new availability record.
     *
     * @param array $data
     * @return MemberAvailability
     */
    public function createAvailability(array $data): MemberAvailability
    {
        $availability = MemberAvailability::create($data);
        
        // Clear relevant caches
        $this->clearAvailabilityCache();
        $this->clearMemberAvailabilityCache($data['member_id']);
        
        return $availability;
    }

    /**
     * Update an availability record.
     *
     * @param int $id
     * @param array $data
     * @return MemberAvailability
     */
    public function updateAvailability(int $id, array $data): MemberAvailability
    {
        $availability = MemberAvailability::findOrFail($id);
        $memberId = $availability->member_id;
        
        $availability->update($data);
        
        // Clear specific availability cache
        $cacheKey = CacheService::generateKey('availability', 'id', ['id' => $id]);
        CacheService::forget($cacheKey);
        
        // Clear other relevant caches
        $this->clearAvailabilityCache();
        $this->clearMemberAvailabilityCache($memberId);
        
        return $availability;
    }

    /**
     * Delete an availability record.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAvailability(int $id): bool
    {
        $availability = MemberAvailability::findOrFail($id);
        $memberId = $availability->member_id;
        
        $result = $availability->delete();
        
        // Clear caches
        $this->clearAvailabilityCache();
        $this->clearMemberAvailabilityCache($memberId);
        
        return $result;
    }

    /**
     * Get availability records for a specific member.
     *
     * @param int $memberId
     * @param bool $currentOnly
     * @return Collection
     */
    public function getAvailabilityForMember(int $memberId, bool $currentOnly = true): Collection
    {
        $cacheKey = CacheService::generateKey('member', 'availability', [
            'member_id' => $memberId,
            'current_only' => $currentOnly
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId, $currentOnly) {
            $member = Member::findOrFail($memberId);
            
            $query = $member->availability();
            
            if ($currentOnly) {
                $query->current();
            }
            
            return $query->orderBy('day_of_week')->orderBy('start_time')->get();
        });
    }

    /**
     * Get members available at a specific day and time.
     *
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersAvailableAt(string $day, string $startTime, string $endTime, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('availability', 'members_at', [
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($day, $startTime, $endTime, $filters, $perPage) {
            $memberIds = MemberAvailability::forDay($day)
                ->overlappingTime($startTime, $endTime)
                ->current()
                ->pluck('member_id')
                ->unique();
                
            $query = Member::whereIn('id', $memberIds);
            
            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if (isset($filters['status'])) {
                $query->where('membership_status', $filters['status']);
            }
            
            return $query->orderBy('last_name')->orderBy('first_name')->paginate($perPage);
        });
    }

    /**
     * Get availability summary for a member.
     *
     * @param int $memberId
     * @return array
     */
    public function getAvailabilitySummary(int $memberId): array
    {
        $cacheKey = CacheService::generateKey('member', 'availability_summary', ['member_id' => $memberId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId) {
            $member = Member::findOrFail($memberId);
            
            $availabilityRecords = $member->currentAvailability()->get();
            
            $summary = [
                'days' => [
                    'monday' => [],
                    'tuesday' => [],
                    'wednesday' => [],
                    'thursday' => [],
                    'friday' => [],
                    'saturday' => [],
                    'sunday' => [],
                ],
                'total_hours' => 0,
            ];
            
            foreach ($availabilityRecords as $record) {
                $day = $record->day_of_week;
                $timeRange = $record->time_range;
                $durationMinutes = $record->duration_minutes;
                
                $summary['days'][$day][] = [
                    'id' => $record->id,
                    'time_range' => $timeRange,
                    'duration_minutes' => $durationMinutes,
                    'is_recurring' => $record->is_recurring,
                    'notes' => $record->notes,
                ];
                
                $summary['total_hours'] += $durationMinutes / 60;
            }
            
            $summary['total_hours'] = round($summary['total_hours'], 1);
            
            return $summary;
        });
    }

    /**
     * Check if a member is available at a specific day and time.
     *
     * @param int $memberId
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isMemberAvailableAt(int $memberId, string $day, string $startTime, string $endTime): bool
    {
        $cacheKey = CacheService::generateKey('member', 'is_available', [
            'member_id' => $memberId,
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId, $day, $startTime, $endTime) {
            $count = MemberAvailability::where('member_id', $memberId)
                ->forDay($day)
                ->overlappingTime($startTime, $endTime)
                ->current()
                ->count();
                
            return $count > 0;
        });
    }

    /**
     * Find members available for a specific event.
     *
     * @param string $day
     * @param string $startTime
     * @param string $endTime
     * @param array $skillIds
     * @param array $spiritualGiftIds
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function findAvailableMembersForEvent(string $day, string $startTime, string $endTime, array $skillIds = [], array $spiritualGiftIds = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('availability', 'members_for_event', [
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'skill_ids' => implode(',', $skillIds),
            'spiritual_gift_ids' => implode(',', $spiritualGiftIds),
            'page' => request()->get('page', 1),
            'perPage' => $perPage
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($day, $startTime, $endTime, $skillIds, $spiritualGiftIds, $perPage) {
            // First, find members who are available at the specified time
            $availableMemberIds = MemberAvailability::forDay($day)
                ->overlappingTime($startTime, $endTime)
                ->current()
                ->pluck('member_id')
                ->unique();
                
            $query = Member::whereIn('id', $availableMemberIds)
                ->where('membership_status', 'active');
                
            // If skill IDs are provided, filter by members with those skills
            if (!empty($skillIds)) {
                $query->whereHas('skills', function ($q) use ($skillIds) {
                    $q->whereIn('skills.id', $skillIds);
                });
            }
            
            // If spiritual gift IDs are provided, filter by members with those gifts
            if (!empty($spiritualGiftIds)) {
                $query->whereHas('spiritualGifts', function ($q) use ($spiritualGiftIds) {
                    $q->whereIn('spiritual_gifts.id', $spiritualGiftIds);
                });
            }
            
            return $query->with(['skills', 'spiritualGifts'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($perPage);
        });
    }

    /**
     * Clear all availability-related caches
     *
     * @return void
     */
    private function clearAvailabilityCache(): void
    {
        CacheService::forgetByPattern('availability_');
    }

    /**
     * Clear member-specific availability caches
     *
     * @param int $memberId
     * @return void
     */
    private function clearMemberAvailabilityCache(int $memberId): void
    {
        $cacheKey = CacheService::generateKey('member', 'availability', ['member_id' => $memberId]);
        CacheService::forget($cacheKey);
        
        $summaryCacheKey = CacheService::generateKey('member', 'availability_summary', ['member_id' => $memberId]);
        CacheService::forget($summaryCacheKey);
        
        CacheService::forgetByPattern('member_is_available_' . $memberId);
    }
}
