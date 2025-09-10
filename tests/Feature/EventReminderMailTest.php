<?php

namespace Tests\Feature;

use App\Mail\EventReminderMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventReminderMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_reminder_mail_is_queued(): void
    {
        Mail::fake();

        $data = [
            'event' => (object) [
                'id' => 1,
                'title' => 'Sunday Service',
                'event_date' => '2025-01-01',
                'start_time' => '10:00:00',
            ],
            'message' => 'Reminder message',
        ];

        Mail::to('jane@example.com')->queue(new EventReminderMail($data));

        Mail::assertQueued(EventReminderMail::class, function ($mail) {
            return $mail->hasTo('jane@example.com');
        });
    }
}
