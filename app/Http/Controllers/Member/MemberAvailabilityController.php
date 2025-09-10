<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\MemberAvailabilityRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;

class MemberAvailabilityController extends Controller
{
    protected $memberAvailabilityRepository;

    /**
     * Create a new controller instance.
     *
     * @param MemberAvailabilityRepositoryInterface $memberAvailabilityRepository
     * @return void
     */
    public function __construct(MemberAvailabilityRepositoryInterface $memberAvailabilityRepository)
    {
        $this->middleware('auth:api');
        $this->memberAvailabilityRepository = $memberAvailabilityRepository;
    }

    /**
     * Get availability for a specific member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberAvailability($memberId)
    {
        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $availability = $this->memberAvailabilityRepository->getMemberAvailability($memberId);

        return response()->json([
            'status' => 'success',
            'data' => $availability
        ]);
    }

    /**
     * Update or create availability for a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMemberAvailability(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_availability') && Auth::id() != $memberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:0,6',
            'time_slots' => 'required|array',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.availability_type' => 'required|in:available,unavailable,preferred',
            'time_slots.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $availability = $this->memberAvailabilityRepository->updateMemberAvailability(
            $memberId,
            $request->day_of_week,
            $request->time_slots
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Member availability updated successfully',
            'data' => $availability
        ]);
    }

    /**
     * Delete a specific availability time slot for a member.
     *
     * @param int $memberId
     * @param int $availabilityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvailabilityTimeSlot($memberId, $availabilityId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_availability') && Auth::id() != $memberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $result = $this->memberAvailabilityRepository->deleteAvailabilityTimeSlot($memberId, $availabilityId);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Availability time slot not found or could not be deleted'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Availability time slot deleted successfully'
        ]);
    }

    /**
     * Clear all availability for a member.
     *
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearMemberAvailability($memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_availability') && Auth::id() != $memberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $this->memberAvailabilityRepository->clearMemberAvailability($memberId);

        return response()->json([
            'status' => 'success',
            'message' => 'Member availability cleared successfully'
        ]);
    }

    /**
     * Find available members for a specific time slot.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findAvailableMembers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
            'spiritual_gifts' => 'nullable|array',
            'spiritual_gifts.*' => 'exists:spiritual_gifts,id',
            'availability_type' => 'nullable|in:available,preferred',
            'per_page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $filters = [
            'skills' => $request->skills,
            'interests' => $request->interests,
            'spiritual_gifts' => $request->spiritual_gifts,
            'availability_type' => $request->availability_type ?? 'available',
        ];

        $perPage = $request->input('per_page', 15);

        $availableMembers = $this->memberAvailabilityRepository->findAvailableMembers(
            $request->day_of_week,
            $request->start_time,
            $request->end_time,
            $filters,
            $perPage
        );

        return response()->json([
            'status' => 'success',
            'data' => $availableMembers
        ]);
    }

    /**
     * Get availability statistics for all members.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailabilityStatistics()
    {
        $statistics = $this->memberAvailabilityRepository->getAvailabilityStatistics();

        return response()->json([
            'status' => 'success',
            'data' => $statistics
        ]);
    }

    /**
     * Copy availability from one day to another for a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyDayAvailability(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_availability') && Auth::id() != $memberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'source_day' => 'required|integer|between:0,6',
            'target_day' => 'required|integer|between:0,6|different:source_day',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        $result = $this->memberAvailabilityRepository->copyDayAvailability(
            $memberId,
            $request->source_day,
            $request->target_day
        );

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to copy availability'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Availability copied successfully',
            'data' => $this->memberAvailabilityRepository->getMemberAvailability($memberId)
        ]);
    }

    /**
     * Import availability from a calendar file (iCal).
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function importAvailabilityFromCalendar(Request $request, $memberId)
    {
        // Check permission
        if (!Auth::user()->can('manage_member_availability') && Auth::id() != $memberId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'calendar_file' => 'required|file|mimes:ics',
            'default_availability' => 'required|in:available,unavailable,preferred',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $member = Member::find($memberId);

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member not found'
            ], 404);
        }

        try {
            $result = $this->memberAvailabilityRepository->importAvailabilityFromCalendar(
                $memberId,
                $request->file('calendar_file'),
                $request->default_availability
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Calendar imported successfully',
                'data' => [
                    'imported_events' => $result['imported_events'],
                    'availability' => $result['availability']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import calendar: ' . $e->getMessage()
            ], 500);
        }
    }
}
