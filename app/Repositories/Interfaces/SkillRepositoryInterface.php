<?php

namespace App\Repositories\Interfaces;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SkillRepositoryInterface
{
    /**
     * Get all skills.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllSkills(array $filters = []): Collection;

    /**
     * Get paginated skills.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedSkills(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get skill by ID.
     *
     * @param int $id
     * @return Skill|null
     */
    public function getSkillById(int $id): ?Skill;

    /**
     * Create a new skill.
     *
     * @param array $data
     * @return Skill
     */
    public function createSkill(array $data): Skill;

    /**
     * Update a skill.
     *
     * @param int $id
     * @param array $data
     * @return Skill
     */
    public function updateSkill(int $id, array $data): Skill;

    /**
     * Delete a skill.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSkill(int $id): bool;

    /**
     * Get all skill categories.
     *
     * @return array
     */
    public function getAllCategories(): array;

    /**
     * Get skills by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getSkillsByCategory(string $category): Collection;

    /**
     * Get skills for a specific member.
     *
     * @param int $memberId
     * @return Collection
     */
    public function getSkillsForMember(int $memberId): Collection;

    /**
     * Get members with a specific skill.
     *
     * @param int $skillId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMembersWithSkill(int $skillId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
