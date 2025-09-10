<?php

namespace App\Repositories;

use App\Models\Interest;
use App\Models\Member;
use App\Repositories\Interfaces\InterestRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InterestRepository implements InterestRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all interests.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllInterests(array $filters = []): Collection
    {
        $cacheKey = CacheService::generateKey('interest', 'all', ['filters' => $filters]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($filters) {
            $query = Interest::query();
            
            if (isset($filters['active'])) {
                $query->where('is_active', $filters['active']);
            }
            
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            }
            
            return $query->orderBy('name')->get();
        });
    }

    /**
     * Get paginated interests.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedInterests(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('interest', 'paginated', [
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = Interest::query();
            
            if (isset($filters['active'])) {
                $query->where('is_active', $filters['active']);
            }
            
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }
            
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                      ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            }
            
            return $query->orderBy('name')->paginate($perPage);
        });
    }

    /**
     * Get interest by ID.
     *
     * @param int $id
     * @return Interest|null
     */
    public function getInterestById(int $id): ?Interest
    {
        $cacheKey = CacheService::generateKey('interest', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return Interest::with('members')->find($id);
        });
    }

    /**
     * Create a new interest.
     *
     * @param array $data
     * @return Interest
     */
    public function createInterest(array $data): Interest
    {
        $interest = Interest::create($data);
        
        // Clear relevant caches
        $this->clearInterestCache();
        
        return $interest;
    }

    /**
     * Update an interest.
     *
     * @param int $id
     * @param array $data
     * @return Interest
     */
    public function updateInterest(int $id, array $data): Interest
    {
        $interest = Interest::findOrFail($id);
        $interest->update($data);
        
        // Clear specific interest cache
        $cacheKey = CacheService::generateKey('interest', 'id', ['id' => $id]);
        CacheService::forget($cacheKey);
        
        // Clear other relevant caches
        $this->clearInterestCache();
        
        return $interest;
    }

    /**
     * Delete an interest.
     *
     * @param int $id
     * @return bool
     */
    public function deleteInterest(int $id): bool
    {
        $interest = Interest::findOrFail($id);
        $result = $interest->delete();
        
        // Clear caches
        $this->clearInterestCache();
        
        return $result;
    }

    /**
     * Get all interest categories.
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        $cacheKey = CacheService::generateKey('interest', 'categories');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return Interest::select('category')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        });
    }

    /**
     * Get interests by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getInterestsByCategory(string $category): Collection
    {
        $cacheKey = CacheService::generateKey('interest', 'category', ['category' => $category]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($category) {
            return Interest::where('category', $category)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get interests for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getInterestsForMember(int $memberId): Collection
    {
        $cacheKey = CacheService::generateKey('member', 'interests', ['member_id' => $memberId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId) {
            $member = Member::findOrFail($memberId);
            return $member->interests()->get();
        });
    }

    /**
     * Get members with a specific interest.
     *
     * @param int $interestId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithInterest(int $interestId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('interest', 'members', [
            'interest_id' => $interestId,
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($interestId, $filters, $perPage) {
            $interest = Interest::findOrFail($interestId);
            
            $query = $interest->members();
            
            if (isset($filters['interest_level'])) {
                $query->wherePivot('interest_level', $filters['interest_level']);
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
     * Clear all interest-related caches
     *
     * @return void
     */
    private function clearInterestCache(): void
    {
        CacheService::forgetByPattern('interest_');
    }
}
