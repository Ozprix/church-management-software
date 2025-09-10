<?php

namespace App\Repositories;

use App\Models\SpiritualGift;
use App\Models\Member;
use App\Repositories\Interfaces\SpiritualGiftRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SpiritualGiftRepository implements SpiritualGiftRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all spiritual gifts.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllSpiritualGifts(array $filters = []): Collection
    {
        $cacheKey = CacheService::generateKey('spiritual_gift', 'all', ['filters' => $filters]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($filters) {
            $query = SpiritualGift::query();
            
            if (isset($filters['active'])) {
                $query->where('is_active', $filters['active']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('description', 'like', "%{$filters['search']}%")
                      ->orWhere('scripture_reference', 'like', "%{$filters['search']}%");
                });
            }
            
            return $query->orderBy('name')->get();
        });
    }

    /**
     * Get paginated spiritual gifts.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedSpiritualGifts(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('spiritual_gift', 'paginated', [
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = SpiritualGift::query();
            
            if (isset($filters['active'])) {
                $query->where('is_active', $filters['active']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('description', 'like', "%{$filters['search']}%")
                      ->orWhere('scripture_reference', 'like', "%{$filters['search']}%");
                });
            }
            
            return $query->orderBy('name')->paginate($perPage);
        });
    }

    /**
     * Get spiritual gift by ID.
     *
     * @param int $id
     * @return SpiritualGift|null
     */
    public function getSpiritualGiftById(int $id): ?SpiritualGift
    {
        $cacheKey = CacheService::generateKey('spiritual_gift', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return SpiritualGift::with('members')->find($id);
        });
    }

    /**
     * Create a new spiritual gift.
     *
     * @param array $data
     * @return SpiritualGift
     */
    public function createSpiritualGift(array $data): SpiritualGift
    {
        $spiritualGift = SpiritualGift::create($data);
        
        // Clear relevant caches
        $this->clearSpiritualGiftCache();
        
        return $spiritualGift;
    }

    /**
     * Update a spiritual gift.
     *
     * @param int $id
     * @param array $data
     * @return SpiritualGift
     */
    public function updateSpiritualGift(int $id, array $data): SpiritualGift
    {
        $spiritualGift = SpiritualGift::findOrFail($id);
        $spiritualGift->update($data);
        
        // Clear specific spiritual gift cache
        $cacheKey = CacheService::generateKey('spiritual_gift', 'id', ['id' => $id]);
        CacheService::forget($cacheKey);
        
        // Clear other relevant caches
        $this->clearSpiritualGiftCache();
        
        return $spiritualGift;
    }

    /**
     * Delete a spiritual gift.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSpiritualGift(int $id): bool
    {
        $spiritualGift = SpiritualGift::findOrFail($id);
        $result = $spiritualGift->delete();
        
        // Clear caches
        $this->clearSpiritualGiftCache();
        
        return $result;
    }

    /**
     * Get spiritual gifts for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSpiritualGiftsForMember(int $memberId): Collection
    {
        $cacheKey = CacheService::generateKey('member', 'spiritual_gifts', ['member_id' => $memberId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId) {
            $member = Member::findOrFail($memberId);
            return $member->spiritualGifts()->get();
        });
    }

    /**
     * Get members with a specific spiritual gift.
     *
     * @param int $spiritualGiftId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithSpiritualGift(int $spiritualGiftId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('spiritual_gift', 'members', [
            'spiritual_gift_id' => $spiritualGiftId,
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($spiritualGiftId, $filters, $perPage) {
            $spiritualGift = SpiritualGift::findOrFail($spiritualGiftId);
            
            $query = $spiritualGift->members();
            
            if (isset($filters['strength_level'])) {
                $query->wherePivot('strength_level', $filters['strength_level']);
            }
            
            if (isset($filters['is_assessed'])) {
                $query->wherePivot('is_assessed', $filters['is_assessed']);
            }
            
            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            return $query->orderBy('last_name')->orderBy('first_name')->paginate($perPage);
        });
    }

    /**
     * Get spiritual gift distribution statistics.
     *
     * @return array
     */
    public function getSpiritualGiftDistribution(): array
    {
        $cacheKey = CacheService::generateKey('spiritual_gift', 'distribution');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            $gifts = SpiritualGift::where('is_active', true)->get();
            $distribution = [];
            
            foreach ($gifts as $gift) {
                $distribution[] = [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'member_count' => $gift->member_count,
                    'strength_distribution' => $gift->strength_distribution,
                ];
            }
            
            return $distribution;
        });
    }

    /**
     * Assign a spiritual gift to a member.
     *
     * @param int $memberId
     * @param int $spiritualGiftId
     * @param array $data
     * @return bool
     */
    public function assignSpiritualGiftToMember(int $memberId, int $spiritualGiftId, array $data = []): bool
    {
        $member = Member::findOrFail($memberId);
        $spiritualGift = SpiritualGift::findOrFail($spiritualGiftId);
        
        $pivotData = [
            'strength_level' => $data['strength_level'] ?? 'medium',
            'notes' => $data['notes'] ?? null,
            'is_assessed' => $data['is_assessed'] ?? false,
            'assessment_date' => $data['assessment_date'] ?? null,
        ];
        
        // Check if the relationship already exists
        if ($member->spiritualGifts()->where('spiritual_gift_id', $spiritualGiftId)->exists()) {
            $member->spiritualGifts()->updateExistingPivot($spiritualGiftId, $pivotData);
        } else {
            $member->spiritualGifts()->attach($spiritualGiftId, $pivotData);
        }
        
        // Clear relevant caches
        $memberCacheKey = CacheService::generateKey('member', 'spiritual_gifts', ['member_id' => $memberId]);
        CacheService::forget($memberCacheKey);
        
        $giftCacheKey = CacheService::generateKey('spiritual_gift', 'members', ['spiritual_gift_id' => $spiritualGiftId]);
        CacheService::forget($giftCacheKey);
        
        $distributionCacheKey = CacheService::generateKey('spiritual_gift', 'distribution');
        CacheService::forget($distributionCacheKey);
        
        return true;
    }

    /**
     * Remove a spiritual gift from a member.
     *
     * @param int $memberId
     * @param int $spiritualGiftId
     * @return bool
     */
    public function removeSpiritualGiftFromMember(int $memberId, int $spiritualGiftId): bool
    {
        $member = Member::findOrFail($memberId);
        
        $member->spiritualGifts()->detach($spiritualGiftId);
        
        // Clear relevant caches
        $memberCacheKey = CacheService::generateKey('member', 'spiritual_gifts', ['member_id' => $memberId]);
        CacheService::forget($memberCacheKey);
        
        $giftCacheKey = CacheService::generateKey('spiritual_gift', 'members', ['spiritual_gift_id' => $spiritualGiftId]);
        CacheService::forget($giftCacheKey);
        
        $distributionCacheKey = CacheService::generateKey('spiritual_gift', 'distribution');
        CacheService::forget($distributionCacheKey);
        
        return true;
    }

    /**
     * Clear all spiritual gift-related caches
     *
     * @return void
     */
    private function clearSpiritualGiftCache(): void
    {
        CacheService::forgetByPattern('spiritual_gift_');
    }
}
