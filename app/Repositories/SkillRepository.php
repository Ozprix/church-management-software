<?php

namespace App\Repositories;

use App\Models\Skill;
use App\Models\Member;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use App\Services\CacheService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SkillRepository implements SkillRepositoryInterface
{
    /**
     * Cache time in minutes
     */
    const CACHE_TIME = 60; // 1 hour

    /**
     * Get all skills.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllSkills(array $filters = []): Collection
    {
        $cacheKey = CacheService::generateKey('skill', 'all', ['filters' => $filters]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($filters) {
            $query = Skill::query();
            
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
     * Get paginated skills.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedSkills(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('skill', 'paginated', [
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($perPage, $filters) {
            $query = Skill::query();
            
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
     * Get skill by ID.
     *
     * @param int $id
     * @return Skill|null
     */
    public function getSkillById(int $id): ?Skill
    {
        $cacheKey = CacheService::generateKey('skill', 'id', ['id' => $id]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($id) {
            return Skill::with('members')->find($id);
        });
    }

    /**
     * Create a new skill.
     *
     * @param array $data
     * @return Skill
     */
    public function createSkill(array $data): Skill
    {
        $skill = Skill::create($data);
        
        // Clear relevant caches
        $this->clearSkillCache();
        
        return $skill;
    }

    /**
     * Update a skill.
     *
     * @param int $id
     * @param array $data
     * @return Skill
     */
    public function updateSkill(int $id, array $data): Skill
    {
        $skill = Skill::findOrFail($id);
        $skill->update($data);
        
        // Clear specific skill cache
        $cacheKey = CacheService::generateKey('skill', 'id', ['id' => $id]);
        CacheService::forget($cacheKey);
        
        // Clear other relevant caches
        $this->clearSkillCache();
        
        return $skill;
    }

    /**
     * Delete a skill.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSkill(int $id): bool
    {
        $skill = Skill::findOrFail($id);
        $result = $skill->delete();
        
        // Clear caches
        $this->clearSkillCache();
        
        return $result;
    }

    /**
     * Get all skill categories.
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        $cacheKey = CacheService::generateKey('skill', 'categories');
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () {
            return Skill::select('category')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        });
    }

    /**
     * Get skills by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getSkillsByCategory(string $category): Collection
    {
        $cacheKey = CacheService::generateKey('skill', 'category', ['category' => $category]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($category) {
            return Skill::where('category', $category)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get skills for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSkillsForMember(int $memberId): Collection
    {
        $cacheKey = CacheService::generateKey('member', 'skills', ['member_id' => $memberId]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($memberId) {
            $member = Member::findOrFail($memberId);
            return $member->skills()->get();
        });
    }

    /**
     * Get members with a specific skill.
     *
     * @param int $skillId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithSkill(int $skillId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = CacheService::generateKey('skill', 'members', [
            'skill_id' => $skillId,
            'page' => request()->get('page', 1),
            'perPage' => $perPage,
            'filters' => $filters
        ]);
        
        return CacheService::remember($cacheKey, self::CACHE_TIME, function () use ($skillId, $filters, $perPage) {
            $skill = Skill::findOrFail($skillId);
            
            $query = $skill->members();
            
            if (isset($filters['proficiency_level'])) {
                $query->wherePivot('proficiency_level', $filters['proficiency_level']);
            }
            
            if (isset($filters['is_verified'])) {
                $query->wherePivot('is_verified', $filters['is_verified']);
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
     * Clear all skill-related caches
     *
     * @return void
     */
    private function clearSkillCache(): void
    {
        CacheService::forgetByPattern('skill_');
    }
}
