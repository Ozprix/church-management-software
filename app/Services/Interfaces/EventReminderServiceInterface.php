<?php

namespace App\Services\Interfaces;

use App\Models\EventReminder;
use Illuminate\Database\Eloquent\Collection;

interface EventReminderServiceInterface
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
     * Get pending reminders that need to be sent.
     *
     * @return Collection
     */
    public function getPendingReminders(): Collection;
    
    /**
     * Process and send reminders that are due.
     *
     * @return int Number of reminders processed
     */
    public function processReminderQueue(): int;
    
    /**
     * Send a specific reminder.
     *
     * @param EventReminder $reminder
     * @return bool
     */
    public function sendReminder(EventReminder $reminder): bool;
}
