<?php

namespace App\Repositories;

use App\Models\EventReminder;
use App\Models\GroupEvent;
use App\Repositories\Interfaces\EventReminderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EventReminderRepository implements EventReminderRepositoryInterface
{
    /**
     * Get all reminders for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventReminders(int $eventId): Collection
    {
        return Cache::remember("event_{$eventId}_reminders", 60 * 5, function () use ($eventId) {
            return EventReminder::where('group_event_id', $eventId)
                ->orderBy('reminder_time', 'desc')
                ->get();
        });
    }
    
    /**
     * Get a specific reminder by ID.
     *
     * @param int $reminderId
     * @return EventReminder|null
     */
    public function getReminderById(int $reminderId): ?EventReminder
    {
        return Cache::remember("event_reminder_{$reminderId}", 60 * 5, function () use ($reminderId) {
            return EventReminder::with('event')->find($reminderId);
        });
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
            $event = GroupEvent::find($data['group_event_id']);
            if ($event && $event->event_date && $event->start_time) {
                $eventDateTime = \Carbon\Carbon::parse($event->event_date . ' ' . $event->start_time);
                $data['scheduled_at'] = $eventDateTime->subHours($data['reminder_time']);
            }
        }
        
        $reminder = EventReminder::create($data);
        
        // Clear cache
        $this->clearReminderCache($reminder->group_event_id);
        
        return $reminder;
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
        $reminder = EventReminder::find($reminderId);
        
        if (!$reminder) {
            return null;
        }
        
        // Recalculate scheduled time if reminder time changed
        if (isset($data['reminder_time']) && $data['reminder_time'] != $reminder->reminder_time) {
            $event = $reminder->event;
            if ($event && $event->event_date && $event->start_time) {
                $eventDateTime = \Carbon\Carbon::parse($event->event_date . ' ' . $event->start_time);
                $data['scheduled_at'] = $eventDateTime->subHours($data['reminder_time']);
            }
        }
        
        $reminder->update($data);
        
        // Clear cache
        Cache::forget("event_reminder_{$reminderId}");
        $this->clearReminderCache($reminder->group_event_id);
        
        return $reminder->fresh();
    }
    
    /**
     * Delete a reminder.
     *
     * @param int $reminderId
     * @return bool
     */
    public function deleteReminder(int $reminderId): bool
    {
        $reminder = EventReminder::find($reminderId);
        
        if (!$reminder) {
            return false;
        }
        
        $eventId = $reminder->group_event_id;
        $result = $reminder->delete();
        
        // Clear cache
        Cache::forget("event_reminder_{$reminderId}");
        $this->clearReminderCache($eventId);
        
        return $result;
    }
    
    /**
     * Get unsent reminders for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getUnsentReminders(int $eventId): Collection
    {
        return EventReminder::where('group_event_id', $eventId)
            ->where('is_sent', false)
            ->orderBy('scheduled_at')
            ->get();
    }
    
    /**
     * Get all due reminders across all events.
     *
     * @return Collection
     */
    public function getDueReminders(): Collection
    {
        return EventReminder::with('event')
            ->where('is_sent', false)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();
    }
    
    /**
     * Mark a reminder as sent.
     *
     * @param int $reminderId
     * @return bool
     */
    public function markReminderAsSent(int $reminderId): bool
    {
        $reminder = EventReminder::find($reminderId);
        
        if (!$reminder) {
            return false;
        }
        
        $reminder->is_sent = true;
        $reminder->sent_at = now();
        $result = $reminder->save();
        
        // Clear cache
        Cache::forget("event_reminder_{$reminderId}");
        $this->clearReminderCache($reminder->group_event_id);
        
        return $result;
    }
    
    /**
     * Schedule reminders for an event.
     *
     * @param int $eventId
     * @return void
     */
    public function scheduleEventReminders(int $eventId): void
    {
        $event = GroupEvent::find($eventId);
        
        if (!$event || !$event->send_reminders || !$event->is_active) {
            return;
        }
        
        // Default reminder times (in hours before event)
        $defaultReminderTimes = [24, 2]; // 1 day and 2 hours before
        
        foreach ($defaultReminderTimes as $reminderTime) {
            // Check if a reminder with this time already exists
            $existingReminder = EventReminder::where('group_event_id', $eventId)
                ->where('reminder_time', $reminderTime)
                ->first();
            
            if (!$existingReminder) {
                // Calculate scheduled time
                $eventDateTime = \Carbon\Carbon::parse($event->event_date . ' ' . $event->start_time);
                $scheduledAt = $eventDateTime->copy()->subHours($reminderTime);
                
                // Only create reminder if it's in the future
                if ($scheduledAt->isFuture()) {
                    $this->createReminder([
                        'group_event_id' => $eventId,
                        'reminder_type' => 'email',
                        'reminder_time' => $reminderTime,
                        'scheduled_at' => $scheduledAt,
                        'send_to_all_members' => true,
                        'send_to_registered_only' => $event->registration_required
                    ]);
                }
            }
        }
    }
    
    /**
     * Clear all reminder-related cache for an event.
     *
     * @param int $eventId
     * @return void
     */
    private function clearReminderCache(int $eventId): void
    {
        Cache::forget("event_{$eventId}_reminders");
        Cache::forget("event_{$eventId}_unsent_reminders");
    }
}
