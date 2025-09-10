<?php

namespace App\Repositories;

use App\Models\Member;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberRepository
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all members with caching
     *
     * @return Collection
     */
    public function getAllMembers(): Collection
    {
        $cacheKey = CacheService::generateKey('member', 'all');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return Member::with(['family', 'user'])->get();
        });
    }

    /**
     * Get paginated members with caching
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedMembers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('member', 'paginated', [
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = Member::with(['family', 'user']);
            
            // Apply filters
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['family_id'])) {
                $query->where('family_id', $filters['family_id']);
            }
            
            return $query->orderBy('last_name')->orderBy('first_name')->paginate($perPage);
        });
    }

    /**
     * Get member by ID with caching
     *
     * @param int $id
     * @return Member|null
     */
    public function getMemberById(int $id): ?Member
    {
        $cacheKey = CacheService::generateKey('member', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return Member::with(['family', 'user', 'attendances', 'donations'])->find($id);
        });
    }

    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data): Member
    {
        $member = Member::create($data);
        
        // Clear relevant caches
        $this->clearMemberCache();
        
        return $member;
    }

    /**
     * Update a member
     *
     * @param int $id
     * @param array $data
     * @return Member
     */
    public function updateMember(int $id, array $data): Member
    {
        $member = Member::findOrFail($id);
        $member->update($data);
        
        // Clear specific member cache
        $cacheKey = CacheService::generateKey('member', 'id', ['id' => $id]);
        CacheService::forget($cacheKey);
        
        // Clear other relevant caches
        $this->clearMemberCache();
        
        return $member;
    }

    /**
     * Delete a member
     *
     * @param int $id
     * @return bool
     */
    public function deleteMember(int $id): bool
    {
        $member = Member::findOrFail($id);
        $result = $member->delete();
        
        // Clear caches
        $this->clearMemberCache();
        
        return $result;
    }

    /**
     * Get member statistics with caching
     *
     * @return array
     */
    public function getMemberStatistics(): array
    {
        $cacheKey = CacheService::generateKey('member', 'statistics');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return [
                'total' => Member::count(),
                'active' => Member::where('status', 'active')->count(),
                'inactive' => Member::where('status', 'inactive')->count(),
                'pending' => Member::where('status', 'pending')->count(),
                'by_gender' => [
                    'male' => Member::where('gender', 'male')->count(),
                    'female' => Member::where('gender', 'female')->count(),
                    'other' => Member::whereNotIn('gender', ['male', 'female'])->count(),
                ],
                'recent' => Member::orderBy('created_at', 'desc')->limit(5)->get(),
            ];
        });
    }

    /**
     * Clear all member-related caches
     *
     * @return void
     */
    private function clearMemberCache(): void
    {
        CacheService::forgetByPattern('member_');
    }
}
