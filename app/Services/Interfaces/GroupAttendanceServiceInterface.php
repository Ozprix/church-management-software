<?php

namespace App\Services\Interfaces;

use App\Models\GroupAttendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupAttendanceServiceInterface
{
    /**
     * Get all group attendances with optional pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllGroupAttendances(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    
    /**
     * Get group attendance by ID.
     *
     * @param int $id
     * @return GroupAttendance|null
     */
    public function getGroupAttendanceById(int $id): ?GroupAttendance;
    
    /**
     * Get attendances for a specific group.
     *
     * @param int $groupId
     * @param array $filters
     * @return Collection
     */
    public function getAttendancesByGroupId(int $groupId, array $filters = []): Collection;
    
    /**
     * Create a new group attendance record.
     *
     * @param array $data
     * @return GroupAttendance
     */
    public function createGroupAttendance(array $data): GroupAttendance;
    
    /**
     * Create a new group attendance record with member details.
     *
     * @param array $data
     * @param array $memberDetails
     * @return GroupAttendance
     */
    public function createGroupAttendanceWithDetails(array $data, array $memberDetails): GroupAttendance;
    
    /**
     * Update a group attendance record.
     *
     * @param int $id
     * @param array $data
     * @return GroupAttendance
     */
    public function updateGroupAttendance(int $id, array $data): GroupAttendance;
    
    /**
     * Delete a group attendance record.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroupAttendance(int $id): bool;
    
    /**
     * Add an attendance detail to a group attendance record.
     *
     * @param int $attendanceId
     * @param array $data
     * @return bool
     */
    public function addAttendanceDetail(int $attendanceId, array $data): bool;
    
    /**
     * Add multiple attendance details to a group attendance record.
     *
     * @param int $attendanceId
     * @param array $detailsData
     * @return bool
     */
    public function addBulkAttendanceDetails(int $attendanceId, array $detailsData): bool;
    
    /**
     * Update an attendance detail.
     *
     * @param int $detailId
     * @param array $data
     * @return bool
     */
    public function updateAttendanceDetail(int $detailId, array $data): bool;
    
    /**
     * Remove an attendance detail.
     *
     * @param int $detailId
     * @return bool
     */
    public function removeAttendanceDetail(int $detailId): bool;
    
    /**
     * Get attendance statistics for a group.
     *
     * @param int $groupId
     * @param string $period
     * @return array
     */
    public function getGroupAttendanceStats(int $groupId, string $period = '3months'): array;
    
    /**
     * Get attendance statistics for a member.
     *
     * @param int $memberId
     * @param string $period
     * @return array
     */
    public function getMemberAttendanceStats(int $memberId, string $period = '3months'): array;
}
