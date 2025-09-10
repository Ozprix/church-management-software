<?php

namespace App\Services;

use App\Models\EventReminder;
use App\Models\GroupEvent;
use App\Models\Member;
use App\Repositories\Interfaces\EventReminderRepositoryInterface;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use App\Services\Interfaces\EventReminderServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\EventReminderMail;

class EventReminderService implements EventReminderServiceInterface
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
     * @var GroupMemberRepositoryInterface
     */
    protected $groupMemberRepository;

    /**
     * @var EventRegistrationRepositoryInterface
     */
    protected $eventRegistrationRepository;

    /**
     * EventReminderService constructor.
     *
     * @param EventReminderRepositoryInterface $eventReminderRepository
     * @param GroupEventRepositoryInterface $groupEventRepository
     * @param GroupMemberRepositoryInterface $groupMemberRepository
     * @param EventRegistrationRepositoryInterface $eventRegistrationRepository
     */
    public function __construct(
        EventReminderRepositoryInterface $eventReminderRepository,
        GroupEventRepositoryInterface $groupEventRepository,
        GroupMemberRepositoryInterface $groupMemberRepository,
        EventRegistrationRepositoryInterface $eventRegistrationRepository
    ) {
        $this->eventReminderRepository = $eventReminderRepository;
        $this->groupEventRepository = $groupEventRepository;
        $this->groupMemberRepository = $groupMemberRepository;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    /**
     * Get all reminders for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventReminders(int $eventId): Collection
    {
        return $this->eventReminderRepository->getEventReminders($eventId);
    }

    /**
     * Get a specific reminder by ID.
     *
     * @param int $reminderId
     * @return EventReminder|null
     */
    public function getReminderById(int $reminderId): ?EventReminder
    {
        return $this->eventReminderRepository->getReminderById($reminderId);
    }

    /**
     * Create a new reminder for an event.
     *
     * @param array $data
     * @return EventReminder
     */
    public function createReminder(array $data): EventReminder
    {
        // Calculate scheduled time if not provided
        if (!isset($data['scheduled_at'])) {
            $event = $this->groupEventRepository->getGroupEventById($data['group_event_id']);
            $eventDateTime = Carbon::parse($event->event_date . ' ' . $event->start_time);
            $reminderMinutes = $data['reminder_time'] ?? 60; // Default to 1 hour before
            $data['scheduled_at'] = $eventDateTime->copy()->subMinutes($reminderMinutes);
        }

        return $this->eventReminderRepository->createReminder($data);
    }

    /**
     * Update an existing reminder.
     *
     * @param int $reminderId
     * @param array $data
     * @return EventReminder|null
     */
    public function updateReminder(int $reminderId, array $data): ?EventReminder
    {
        // Recalculate scheduled time if reminder time has changed
        if (isset($data['reminder_time'])) {
            $reminder = $this->eventReminderRepository->getReminderById($reminderId);
            $event = $this->groupEventRepository->getGroupEventById($reminder->group_event_id);
            $eventDateTime = Carbon::parse($event->event_date . ' ' . $event->start_time);
            $reminderMinutes = $data['reminder_time'];
            $data['scheduled_at'] = $eventDateTime->copy()->subMinutes($reminderMinutes);
        }

        return $this->eventReminderRepository->updateReminder($reminderId, $data);
    }

    /**
     * Delete a reminder.
     *
     * @param int $reminderId
     * @return bool
     */
    public function deleteReminder(int $reminderId): bool
    {
        return $this->eventReminderRepository->deleteReminder($reminderId);
    }

    /**
     * Get pending reminders that need to be sent.
     *
     * @return Collection
     */
    public function getPendingReminders(): Collection
    {
        return $this->eventReminderRepository->getPendingReminders();
    }

    /**
     * Process and send reminders that are due.
     *
     * @return int Number of reminders processed
     */
    public function processReminderQueue(): int
    {
        $now = Carbon::now();
        $pendingReminders = $this->eventReminderRepository->getPendingReminders();
        $count = 0;

        foreach ($pendingReminders as $reminder) {
            // Check if the reminder is due to be sent
            if ($reminder->scheduled_at <= $now) {
                $result = $this->sendReminder($reminder);
                
                if ($result) {
                    // Mark as sent
                    $this->eventReminderRepository->updateReminder($reminder->id, [
                        'is_sent' => true,
                        'sent_at' => $now
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Send a specific reminder.
     *
     * @param EventReminder $reminder
     * @return bool
     */
    public function sendReminder(EventReminder $reminder): bool
    {
        try {
            $event = $this->groupEventRepository->getGroupEventById($reminder->group_event_id);
            
            if (!$event) {
                Log::error("Failed to send reminder: Event not found", ['reminder_id' => $reminder->id]);
                return false;
            }

            // Get recipients
            $recipients = $this->getRecipients($reminder, $event);
            
            if ($recipients->isEmpty()) {
                Log::warning("No recipients found for reminder", ['reminder_id' => $reminder->id]);
                return false;
            }

            // Prepare reminder data
            $reminderData = [
                'event' => $event,
                'message' => $reminder->custom_message ?: "Reminder: {$event->title} is scheduled for {$event->event_date} at {$event->start_time}",
                'reminder_type' => $reminder->reminder_type
            ];

            // Send based on reminder type
            switch ($reminder->reminder_type) {
                case 'email':
                    $this->sendEmailReminders($recipients, $reminderData);
                    break;
                case 'sms':
                    $this->sendSmsReminders($recipients, $reminderData);
                    break;
                case 'notification':
                    $this->sendNotificationReminders($recipients, $reminderData);
                    break;
                default:
                    Log::error("Unknown reminder type", ['reminder_id' => $reminder->id, 'type' => $reminder->reminder_type]);
                    return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send reminder: {$e->getMessage()}", [
                'reminder_id' => $reminder->id,
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Get the recipients for a reminder.
     *
     * @param EventReminder $reminder
     * @param GroupEvent $event
     * @return Collection
     */
    protected function getRecipients(EventReminder $reminder, GroupEvent $event): Collection
    {
        // If sending to registered members only
        if ($reminder->send_to_registered_only) {
            return $this->eventRegistrationRepository->getRegisteredMembers($event->id);
        }
        
        // If sending to all group members
        if ($reminder->send_to_all_members) {
            return $this->groupMemberRepository->getGroupMembers($event->group_id);
        }
        
        // Default: send to registered members
        return $this->eventRegistrationRepository->getRegisteredMembers($event->id);
    }

    /**
     * Send email reminders to recipients.
     *
     * @param Collection $recipients
     * @param array $reminderData
     * @return void
     */
    protected function sendEmailReminders(Collection $recipients, array $reminderData): void
    {
        foreach ($recipients as $recipient) {
            if ($recipient->email) {
                Mail::to($recipient->email)->queue(new EventReminderMail($reminderData));
                Log::info("Queued email reminder to {$recipient->email}", [
                    'event_id' => $reminderData['event']->id,
                    'recipient_id' => $recipient->id
                ]);
            }
        }
    }

    /**
     * Send SMS reminders to recipients.
     *
     * @param Collection $recipients
     * @param array $reminderData
     * @return void
     */
    protected function sendSmsReminders(Collection $recipients, array $reminderData): void
    {
        foreach ($recipients as $recipient) {
            if ($recipient->phone) {
                // TODO: Implement actual SMS sending logic using a service like Twilio
                Log::info("SMS reminder sent to {$recipient->phone}", [
                    'event_id' => $reminderData['event']->id,
                    'recipient_id' => $recipient->id
                ]);
            }
        }
    }

    /**
     * Send in-app notification reminders to recipients.
     *
     * @param Collection $recipients
     * @param array $reminderData
     * @return void
     */
    protected function sendNotificationReminders(Collection $recipients, array $reminderData): void
    {
        // TODO: Implement actual notification sending logic
        // Notification::send($recipients, new EventReminderNotification($reminderData));
        
        foreach ($recipients as $recipient) {
            Log::info("Notification reminder sent to user", [
                'event_id' => $reminderData['event']->id,
                'recipient_id' => $recipient->id
            ]);
        }
    }
}
