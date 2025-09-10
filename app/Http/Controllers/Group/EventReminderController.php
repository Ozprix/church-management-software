<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventReminderRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventReminderController extends Controller
{
    /**
     * @var EventReminderRepositoryInterface
     */
    protected $eventReminderRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * EventReminderController constructor.
     *
     * @param EventReminderRepositoryInterface $eventReminderRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     */
    public function __construct(
        EventReminderRepositoryInterface $eventReminderRepository,
        GroupEventRepositoryInterface $groupEventRepository
    ) {
        $this->eventReminderRepository = $eventReminderRepository;
        $this->groupEventRepository = $groupEventRepository;
    }

    /**
     * Display a listing of the reminders for an event.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $reminders = $this->eventReminderRepository->getEventReminders($eventId);

        return response()->json([
            'status' => 'success',
            'data' => $reminders
        ]);
    }

    /**
     * Store a newly created reminder in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'reminder_type' => 'required|string|in:email,sms,notification',
            'reminder_time' => 'required|integer|min:5|max:10080', // 5 minutes to 7 days in minutes
            'custom_message' => 'nullable|string|max:500',
            'send_to_all_members' => 'boolean',
            'send_to_registered_only' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['group_event_id'] = $eventId;
            $data['is_sent'] = false;
            
            // Calculate scheduled time based on event date and reminder time
            $eventDateTime = $event->event_date . ' ' . $event->start_time;
            $reminderMinutes = $request->reminder_time;
            $data['scheduled_at'] = \Carbon\Carbon::parse($eventDateTime)->subMinutes($reminderMinutes);

            $reminder = $this->eventReminderRepository->createReminder($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Reminder created successfully',
                'data' => $reminder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified reminder.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $reminderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $eventId, $reminderId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $reminder = $this->eventReminderRepository->getReminderById($reminderId);

        if (!$reminder || $reminder->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $reminder
        ]);
    }

    /**
     * Update the specified reminder in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @param int $reminderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $eventId, $reminderId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if reminder exists and belongs to the event
        $reminder = $this->eventReminderRepository->getReminderById($reminderId);
        if (!$reminder || $reminder->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder not found'
            ], 404);
        }

        // Don't allow updating if reminder has already been sent
        if ($reminder->is_sent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a reminder that has already been sent'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reminder_type' => 'sometimes|required|string|in:email,sms,notification',
            'reminder_time' => 'sometimes|required|integer|min:5|max:10080', // 5 minutes to 7 days in minutes
            'custom_message' => 'nullable|string|max:500',
            'send_to_all_members' => 'boolean',
            'send_to_registered_only' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
            // Recalculate scheduled time if reminder time has changed
            if ($request->has('reminder_time')) {
                $eventDateTime = $event->event_date . ' ' . $event->start_time;
                $reminderMinutes = $request->reminder_time;
                $data['scheduled_at'] = \Carbon\Carbon::parse($eventDateTime)->subMinutes($reminderMinutes);
            }

            $updatedReminder = $this->eventReminderRepository->updateReminder($reminderId, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Reminder updated successfully',
                'data' => $updatedReminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified reminder from storage.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $reminderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $eventId, $reminderId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if reminder exists and belongs to the event
        $reminder = $this->eventReminderRepository->getReminderById($reminderId);
        if (!$reminder || $reminder->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder not found'
            ], 404);
        }

        // Don't allow deleting if reminder has already been sent
        if ($reminder->is_sent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete a reminder that has already been sent'
            ], 400);
        }

        try {
            $result = $this->eventReminderRepository->deleteReminder($reminderId);

            return response()->json([
                'status' => 'success',
                'message' => 'Reminder deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending reminders that need to be sent.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingReminders()
    {
        $reminders = $this->eventReminderRepository->getPendingReminders();

        return response()->json([
            'status' => 'success',
            'data' => $reminders
        ]);
    }

    /**
     * Mark a reminder as sent.
     *
     * @param int $reminderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsSent($reminderId)
    {
        $reminder = $this->eventReminderRepository->getReminderById($reminderId);

        if (!$reminder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder not found'
            ], 404);
        }

        try {
            $data = [
                'is_sent' => true,
                'sent_at' => now()
            ];

            $updatedReminder = $this->eventReminderRepository->updateReminder($reminderId, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Reminder marked as sent',
                'data' => $updatedReminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark reminder as sent',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
