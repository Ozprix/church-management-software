<?php

namespace App\Console\Commands;

use App\Services\Interfaces\EventReminderServiceInterface;
use Illuminate\Console\Command;

class ProcessEventReminders extends Command
{
    protected $signature = 'events:process-reminders';

    protected $description = 'Process and send due event reminders';

    public function handle(EventReminderServiceInterface $eventReminderService): int
    {
        $count = $eventReminderService->processReminderQueue();
        $this->info("Processed {$count} reminders.");
        return self::SUCCESS;
    }
}


