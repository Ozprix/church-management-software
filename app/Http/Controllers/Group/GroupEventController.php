<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\GroupEventServiceInterface;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupEventController extends Controller
{
    /**
     * @var GroupEventServiceInterface
     */
    protected $groupEventService;

    /**
     * GroupEventController constructor.
     *
     * @param GroupEventServiceInterface $groupEventService
     */
    public function __construct(GroupEventServiceInterface $groupEventService)
    {
        $this->groupEventService = $groupEventService;
    }

    /**
     * Display a listing of the group events.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, int $groupId)
    {
        // Check if group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $perPage = $request->input('per_page', 10);
        $filter = $request->input('filter', 'all');
        $type = $request->input('type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($filter === 'upcoming') {
            $events = $this->groupEventService->getUpcomingEvents($groupId, $perPage);
        } elseif ($filter === 'past') {
            $events = $this->groupEventService->getPastEvents($groupId, $perPage);
        } elseif ($type) {
            $events = $this->groupEventService->getEventsByType($groupId, $type, $perPage);
        } elseif ($startDate && $endDate) {
            $events = $this->groupEventService->getEventsByDateRange($groupId, $startDate, $endDate, $perPage);
        } else {
            $events = $this->groupEventService->getAllGroupEvents($groupId, $perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * Store a newly created group event in storage.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, int $groupId)
    {
        // Check if group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|string|max:50',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|required_if:is_recurring,true|string|in:daily,weekly,monthly,yearly',
            'recurrence_day' => 'nullable|string|max:20',
            'recurrence_end_date' => 'nullable|date|after:event_date',
            'notify_members' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data
        $data = $request->all();
        $data['group_id'] = $groupId;
        $data['created_by'] = Auth::id();

        try {
            $event = $this->groupEventService->createGroupEvent($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Group event created successfully',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create group event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified group event.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $groupId, int $eventId)
    {
        // Check if group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $event = $this->groupEventService->getGroupEventById($eventId);

        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group event not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $event
        ]);
    }

    /**
     * Update the specified group event in storage.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $groupId, int $eventId)
    {
        // Check if group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if event exists and belongs to the group
        $event = $this->groupEventService->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group event not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|required|date',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'event_type' => 'sometimes|required|string|max:50',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|required_if:is_recurring,true|string|in:daily,weekly,monthly,yearly',
            'recurrence_day' => 'nullable|string|max:20',
            'recurrence_end_date' => 'nullable|date|after:event_date',
            'notify_members' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updatedEvent = $this->groupEventService->updateGroupEvent($eventId, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Group event updated successfully',
                'data' => $updatedEvent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update group event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified group event from storage.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $groupId, int $eventId)
    {
        // Check if group exists
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        // Check if event exists and belongs to the group
        $event = $this->groupEventService->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group event not found'
            ], 404);
        }

        try {
            $result = $this->groupEventService->deleteGroupEvent($eventId);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Group event deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete group event'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete group event',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
