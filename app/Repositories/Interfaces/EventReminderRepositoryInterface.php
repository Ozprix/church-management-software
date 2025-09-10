<?php

namespace App\Repositories\Interfaces;

use App\Models\EventReminder;
use Illuminate\Database\Eloquent\Collection;

interface EventReminderRepositoryInterface
{
    /**
     * Get all reminders for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventReminders(int $eventId): Collection;
    
    /**
     * Get a specific reminder by ID.
     *
     * @param int $reminderId
     * @return EventReminder|null
     */
    public function getReminderById(int $reminderId): ?EventReminder;
    
    /**
     * Create a new reminder for an event.
     *
     * @param array $data
     * @return EventReminder
     */
    public function createReminder(array $data): EventReminder;
    
    /**
     * Update an existing reminder.
     *
     * @param int $reminderId
     * @param array $data
     * @return EventReminder|null
     */
    public function updateReminder(int $reminderId, array $data): ?EventReminder;
    
    /**
     * Delete a reminder.
     *
     * @param int $reminderId
     * @return bool
     */
    public function deleteReminder(int $reminderId): bool;
    
    /**
     * Get unsent reminders for an event.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getUnsentReminders(int $eventId): Collection;
    
    /**
     * Get all due reminders across all events.
     *
     * @return Collection
     */
    public function getDueReminders(): Collection;
    
    /**
     * Mark a reminder as sent.
     *
     * @param int $reminderId
     * @return bool
     */
    public function markReminderAsSent(int $reminderId): bool;
    
    /**
     * Schedule reminders for an event.
     *
     * @param int $eventId
     * @return void
     */
    public function scheduleEventReminders(int $eventId): void;
}
