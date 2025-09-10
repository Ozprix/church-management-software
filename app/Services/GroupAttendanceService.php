<?php

namespace App\Services;

use App\Models\GroupAttendance;
use App\Repositories\Interfaces\GroupAttendanceRepositoryInterface;
use App\Services\Interfaces\GroupAttendanceServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupAttendanceService implements GroupAttendanceServiceInterface
{
    /**
     * @var GroupAttendanceRepositoryInterface
     */
    protected $groupAttendanceRepository;
    
    /**
     * Create a new service instance.
     *
     * @param GroupAttendanceRepositoryInterface $groupAttendanceRepository
     * @return void
     */
    public function __construct(GroupAttendanceRepositoryInterface $groupAttendanceRepository)
    {
        $this->groupAttendanceRepository = $groupAttendanceRepository;
    }
    
    /**
     * Get all group attendances with optional pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllGroupAttendances(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->groupAttendanceRepository->getAllGroupAttendances($perPage, $filters);
    }
    
    /**
     * Get group attendance by ID.
     *
     * @param int $id
     * @return GroupAttendance|null
     */
    public function getGroupAttendanceById(int $id): ?GroupAttendance
    {
        return $this->groupAttendanceRepository->getGroupAttendanceById($id);
    }
    
    /**
     * Get attendances for a specific group.
     *
     * @param int $groupId
     * @param array $filters
     * @return Collection
     */
    public function getAttendancesByGroupId(int $groupId, array $filters = []): Collection
    {
        return $this->groupAttendanceRepository->getAttendancesByGroupId($groupId, $filters);
    }
    
    /**
     * Create a new group attendance record.
     *
     * @param array $data
     * @return GroupAttendance
     */
    public function createGroupAttendance(array $data): GroupAttendance
    {
        // Set the recorded_by field if not provided
        if (!isset($data['recorded_by']) && Auth::check()) {
            $data['recorded_by'] = Auth::id();
        }
        
        return $this->groupAttendanceRepository->createGroupAttendance($data);
    }
    
    /**
     * Create a new group attendance record with member details.
     *
     * @param array $data
     * @param array $memberDetails
     * @return GroupAttendance
     */
    public function createGroupAttendanceWithDetails(array $data, array $memberDetails): GroupAttendance
    {
        // Start a database transaction
        return DB::transaction(function () use ($data, $memberDetails) {
            // Set the recorded_by field if not provided
            if (!isset($data['recorded_by']) && Auth::check()) {
                $data['recorded_by'] = Auth::id();
            }
            
            // Create the attendance record
            $attendance = $this->groupAttendanceRepository->createGroupAttendance($data);
            
            // Add attendance details for each member
            foreach ($memberDetails as $detail) {
                $detail['group_attendance_id'] = $attendance->id;
                $this->groupAttendanceRepository->addAttendanceDetail($attendance->id, $detail);
            }
            
            // Refresh the attendance record with the updated counts
            return $this->getGroupAttendanceById($attendance->id);
        });
    }
    
    /**
     * Update a group attendance record.
     *
     * @param int $id
     * @param array $data
     * @return GroupAttendance
     */
    public function updateGroupAttendance(int $id, array $data): GroupAttendance
    {
        return $this->groupAttendanceRepository->updateGroupAttendance($id, $data);
    }
    
    /**
     * Delete a group attendance record.
     *
     * @param int $id
     * @return bool
     */
    public function deleteGroupAttendance(int $id): bool
    {
        return $this->groupAttendanceRepository->deleteGroupAttendance($id);
    }
    
    /**
     * Add an attendance detail to a group attendance record.
     *
     * @param int $attendanceId
     * @param array $data
     * @return bool
     */
    public function addAttendanceDetail(int $attendanceId, array $data): bool
    {
        return $this->groupAttendanceRepository->addAttendanceDetail($attendanceId, $data);
    }
    
    /**
     * Add multiple attendance details to a group attendance record.
     *
     * @param int $attendanceId
     * @param array $detailsData
     * @return bool
     */
    public function addBulkAttendanceDetails(int $attendanceId, array $detailsData): bool
    {
        // Start a database transaction
        return DB::transaction(function () use ($attendanceId, $detailsData) {
            $success = true;
            
            foreach ($detailsData as $data) {
                $data['group_attendance_id'] = $attendanceId;
                $result = $this->groupAttendanceRepository->addAttendanceDetail($attendanceId, $data);
                
                if (!$result) {
                    $success = false;
                    break;
                }
            }
            
            return $success;
        });
    }
    
    /**
     * Update an attendance detail.
     *
     * @param int $detailId
     * @param array $data
     * @return bool
     */
    public function updateAttendanceDetail(int $detailId, array $data): bool
    {
        return $this->groupAttendanceRepository->updateAttendanceDetail($detailId, $data);
    }
    
    /**
     * Remove an attendance detail.
     *
     * @param int $detailId
     * @return bool
     */
    public function removeAttendanceDetail(int $detailId): bool
    {
        return $this->groupAttendanceRepository->removeAttendanceDetail($detailId);
    }
    
    /**
     * Get attendance statistics for a group.
     *
     * @param int $groupId
     * @param string $period
     * @return array
     */
    public function getGroupAttendanceStats(int $groupId, string $period = '3months'): array
    {
        return $this->groupAttendanceRepository->getGroupAttendanceStats($groupId, $period);
    }
    
    /**
     * Get attendance statistics for a member.
     *
     * @param int $memberId
     * @param string $period
     * @return array
     */
    public function getMemberAttendanceStats(int $memberId, string $period = '3months'): array
    {
        return $this->groupAttendanceRepository->getMemberAttendanceStats($memberId, $period);
    }
}
