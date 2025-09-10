<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAttendance;
use App\Services\Interfaces\GroupAttendanceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupAttendanceController extends Controller
{
    /**
     * The group attendance service instance.
     *
     * @var GroupAttendanceServiceInterface
     */
    protected $groupAttendanceService;

    /**
     * Create a new controller instance.
     *
     * @param GroupAttendanceServiceInterface $groupAttendanceService
     * @return void
     */
    public function __construct(GroupAttendanceServiceInterface $groupAttendanceService)
    {
        $this->groupAttendanceService = $groupAttendanceService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'group_id' => $request->group_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'meeting_type' => $request->meeting_type,
            'sort_by' => $request->sort_by,
            'sort_dir' => $request->sort_dir ?? 'desc'
        ];

        $attendances = $this->groupAttendanceService->getAllGroupAttendances(
            $request->per_page ?? 15,
            $filters
        );

        return response()->json([
            'status' => 'success',
            'data' => $attendances
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'attendance_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'meeting_type' => 'nullable|in:regular,special,online',
            'notes' => 'nullable|string',
            'member_details' => 'nullable|array',
            'member_details.*.member_id' => 'nullable|exists:members,id',
            'member_details.*.visitor_name' => 'nullable|string|required_without:member_details.*.member_id',
            'member_details.*.visitor_phone' => 'nullable|string',
            'member_details.*.visitor_email' => 'nullable|email',
            'member_details.*.is_first_time' => 'nullable|boolean',
            'member_details.*.attendance_status' => 'nullable|in:present,absent,excused',
            'member_details.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if there's already an attendance record for this group on the same date
        $existingAttendance = GroupAttendance::where('group_id', $request->group_id)
            ->where('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'An attendance record already exists for this group on the specified date'
            ], 422);
        }

        // Create attendance with or without member details
        if ($request->has('member_details') && !empty($request->member_details)) {
            $attendance = $this->groupAttendanceService->createGroupAttendanceWithDetails(
                $request->except('member_details'),
                $request->member_details
            );
        } else {
            $attendance = $this->groupAttendanceService->createGroupAttendance($request->all());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Group attendance record created successfully',
            'data' => $attendance
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $attendance = $this->groupAttendanceService->getGroupAttendanceById($id);

        if (!$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group attendance record not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $attendance
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attendance_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'meeting_type' => 'nullable|in:regular,special,online',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if there's already an attendance record for this group on the same date (if date is being changed)
        if ($request->has('attendance_date')) {
            $attendance = GroupAttendance::findOrFail($id);
            $existingAttendance = GroupAttendance::where('group_id', $attendance->group_id)
                ->where('attendance_date', $request->attendance_date)
                ->where('id', '!=', $id)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An attendance record already exists for this group on the specified date'
                ], 422);
            }
        }

        $attendance = $this->groupAttendanceService->updateGroupAttendance($id, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Group attendance record updated successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->groupAttendanceService->deleteGroupAttendance($id);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete group attendance record'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Group attendance record deleted successfully'
        ]);
    }

    /**
     * Get attendances for a specific group.
     *
     * @param Request $request
     * @param string $groupId
     * @return JsonResponse
     */
    public function getGroupAttendances(Request $request, string $groupId): JsonResponse
    {
        // Check if the group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'meeting_type' => $request->meeting_type,
            'sort_by' => $request->sort_by,
            'sort_dir' => $request->sort_dir ?? 'desc'
        ];

        $attendances = $this->groupAttendanceService->getAttendancesByGroupId($groupId, $filters);

        return response()->json([
            'status' => 'success',
            'data' => $attendances
        ]);
    }

    /**
     * Get attendance statistics for a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return JsonResponse
     */
    public function getGroupAttendanceStats(Request $request, string $groupId): JsonResponse
    {
        // Check if the group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $period = $request->period ?? '3months';
        $stats = $this->groupAttendanceService->getGroupAttendanceStats($groupId, $period);

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Add attendance details to an existing attendance record.
     *
     * @param Request $request
     * @param string $attendanceId
     * @return JsonResponse
     */
    public function addAttendanceDetails(Request $request, string $attendanceId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'details' => 'required|array',
            'details.*.member_id' => 'nullable|exists:members,id',
            'details.*.visitor_name' => 'nullable|string|required_without:details.*.member_id',
            'details.*.visitor_phone' => 'nullable|string',
            'details.*.visitor_email' => 'nullable|email',
            'details.*.is_first_time' => 'nullable|boolean',
            'details.*.attendance_status' => 'nullable|in:present,absent,excused',
            'details.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the attendance record exists
        $attendance = GroupAttendance::find($attendanceId);
        if (!$attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group attendance record not found'
            ], 404);
        }

        $result = $this->groupAttendanceService->addBulkAttendanceDetails($attendanceId, $request->details);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add attendance details'
            ], 500);
        }

        // Get the updated attendance record
        $updatedAttendance = $this->groupAttendanceService->getGroupAttendanceById($attendanceId);

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance details added successfully',
            'data' => $updatedAttendance
        ]);
    }

    /**
     * Update an attendance detail.
     *
     * @param Request $request
     * @param string $attendanceId
     * @param string $detailId
     * @return JsonResponse
     */
    public function updateAttendanceDetail(Request $request, string $attendanceId, string $detailId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|exists:members,id',
            'visitor_name' => 'nullable|string|required_without:member_id',
            'visitor_phone' => 'nullable|string',
            'visitor_email' => 'nullable|email',
            'is_first_time' => 'nullable|boolean',
            'attendance_status' => 'nullable|in:present,absent,excused',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->groupAttendanceService->updateAttendanceDetail($detailId, $request->all());

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update attendance detail'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance detail updated successfully'
        ]);
    }

    /**
     * Remove an attendance detail.
     *
     * @param string $attendanceId
     * @param string $detailId
     * @return JsonResponse
     */
    public function removeAttendanceDetail(string $attendanceId, string $detailId): JsonResponse
    {
        $result = $this->groupAttendanceService->removeAttendanceDetail($detailId);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to remove attendance detail'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance detail removed successfully'
        ]);
    }
}
