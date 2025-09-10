<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventRegistrationController extends Controller
{
    /**
     * @var EventRegistrationRepositoryInterface
     */
    protected $eventRegistrationRepository;

    /**
     * @var GroupEventRepositoryInterface
     */
    protected $groupEventRepository;

    /**
     * EventRegistrationController constructor.
     *
     * @param EventRegistrationRepositoryInterface $eventRegistrationRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     */
    public function __construct(
        EventRegistrationRepositoryInterface $eventRegistrationRepository,
        GroupEventRepositoryInterface $groupEventRepository
    ) {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
        $this->groupEventRepository = $groupEventRepository;
    }

    /**
     * Display a listing of the registrations for an event.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');

        if ($status) {
            $registrations = $this->eventRegistrationRepository->getRegistrationsByStatus($eventId, $status, $perPage);
        } else {
            $registrations = $this->eventRegistrationRepository->getEventRegistrations($eventId, $perPage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $registrations
        ]);
    }

    /**
     * Store a newly created registration in storage.
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

        // Check if registration is open for this event
        if (!$event->is_registration_open) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration is not open for this event',
                'registration_status' => $event->registration_status
            ], 400);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|exists:members,id',
            'guest_name' => 'required_without:member_id|nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:registered,confirmed,attended,cancelled',
            'number_of_guests' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if member is already registered
        if ($request->has('member_id')) {
            $existingRegistration = $this->eventRegistrationRepository->getMemberEventRegistration($eventId, $request->member_id);
            if ($existingRegistration) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Member is already registered for this event',
                    'data' => $existingRegistration
                ], 409);
            }
        }

        // Check guest limits
        $guestCount = $request->input('number_of_guests', 0);
        if ($guestCount > 0) {
            if (!$event->allow_guests) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Guests are not allowed for this event'
                ], 400);
            }

            if ($event->max_guests_per_registration > 0 && $guestCount > $event->max_guests_per_registration) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Maximum {$event->max_guests_per_registration} guests allowed per registration"
                ], 400);
            }
        }

        try {
            $data = $request->all();
            $data['group_event_id'] = $eventId;
            $data['registered_at'] = now();
            $data['status'] = $request->input('status', 'registered');

            $registration = $this->eventRegistrationRepository->createRegistration($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration created successfully',
                'data' => $registration
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified registration.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $registrationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($groupId, $eventId, $registrationId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $registration = $this->eventRegistrationRepository->getRegistrationById($registrationId);

        if (!$registration || $registration->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $registration
        ]);
    }

    /**
     * Update the specified registration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $groupId
     * @param int $eventId
     * @param int $registrationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $groupId, $eventId, $registrationId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if registration exists and belongs to the event
        $registration = $this->eventRegistrationRepository->getRegistrationById($registrationId);
        if (!$registration || $registration->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration not found'
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:registered,confirmed,attended,cancelled',
            'number_of_guests' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'confirmed_at' => 'nullable|date',
            'cancelled_at' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check guest limits
        if ($request->has('number_of_guests')) {
            $guestCount = $request->number_of_guests;
            if ($guestCount > 0) {
                if (!$event->allow_guests) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Guests are not allowed for this event'
                    ], 400);
                }

                if ($event->max_guests_per_registration > 0 && $guestCount > $event->max_guests_per_registration) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Maximum {$event->max_guests_per_registration} guests allowed per registration"
                    ], 400);
                }
            }
        }

        try {
            $data = $request->all();
            
            // Set timestamps based on status changes
            if ($request->has('status')) {
                if ($request->status === 'confirmed' && $registration->status !== 'confirmed') {
                    $data['confirmed_at'] = now();
                } elseif ($request->status === 'cancelled' && $registration->status !== 'cancelled') {
                    $data['cancelled_at'] = now();
                }
            }

            $updatedRegistration = $this->eventRegistrationRepository->updateRegistration($registrationId, $data);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration updated successfully',
                'data' => $updatedRegistration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified registration from storage.
     *
     * @param int $groupId
     * @param int $eventId
     * @param int $registrationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($groupId, $eventId, $registrationId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // Check if registration exists and belongs to the event
        $registration = $this->eventRegistrationRepository->getRegistrationById($registrationId);
        if (!$registration || $registration->group_event_id != $eventId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration not found'
            ], 404);
        }

        try {
            $result = $this->eventRegistrationRepository->deleteRegistration($registrationId);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registrations for a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberRegistrations(Request $request, $memberId)
    {
        $perPage = $request->input('per_page', 15);
        $registrations = $this->eventRegistrationRepository->getMemberRegistrations($memberId, $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $registrations
        ]);
    }

    /**
     * Check if a member is registered for an event.
     *
     * @param int $eventId
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMemberRegistration($eventId, $memberId)
    {
        $registration = $this->eventRegistrationRepository->getMemberEventRegistration($eventId, $memberId);

        return response()->json([
            'status' => 'success',
            'is_registered' => $registration !== null,
            'data' => $registration
        ]);
    }

    /**
     * Get registration statistics for an event.
     *
     * @param int $groupId
     * @param int $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegistrationStats($groupId, $eventId)
    {
        // Check if event exists and belongs to the group
        $event = $this->groupEventRepository->getGroupEventById($eventId);
        if (!$event || $event->group_id != $groupId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        $totalRegistrations = $this->eventRegistrationRepository->countEventRegistrations($eventId);
        $confirmedRegistrations = $this->eventRegistrationRepository->countRegistrationsByStatus($eventId, 'confirmed');
        $attendedRegistrations = $this->eventRegistrationRepository->countRegistrationsByStatus($eventId, 'attended');
        $cancelledRegistrations = $this->eventRegistrationRepository->countRegistrationsByStatus($eventId, 'cancelled');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total' => $totalRegistrations,
                'confirmed' => $confirmedRegistrations,
                'attended' => $attendedRegistrations,
                'cancelled' => $cancelledRegistrations,
                'capacity' => $event->registration_capacity,
                'available_spots' => $event->available_spots,
                'registration_status' => $event->registration_status,
                'is_registration_open' => $event->is_registration_open
            ]
        ]);
    }
}
