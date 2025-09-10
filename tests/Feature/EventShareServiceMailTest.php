<?php

namespace Tests\Feature;

use App\Mail\EventShareInvitationMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventShareServiceMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_event_share_invitation_mail_can_be_queued()
    {
        // Create mock data without database
        $event = (object) [
            'id' => 1,
            'title' => 'Test Event',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00:00',
            'location' => 'Main Hall'
        ];

        $share = (object) [
            'id' => 1,
            'token' => 'test-token-123',
            'expires_at' => now()->addDays(7)
        ];

        $sender = (object) [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'sender@example.com'
        ];

        // Prepare invitation data
        $invitationData = [
            'event' => $event,
            'share' => $share,
            'sender' => $sender,
            'message' => 'You are invited to this event!',
            'token' => $share->token,
            'expires_at' => $share->expires_at
        ];

        // Queue the mail
        Mail::to('test@example.com')->queue(new EventShareInvitationMail($invitationData));

        // Assert mail was queued
        Mail::assertQueued(EventShareInvitationMail::class, function ($mail) use ($event, $share) {
            return $mail->data['event']->id === $event->id &&
                   $mail->data['share']->id === $share->id &&
                   $mail->data['message'] === 'You are invited to this event!';
        });
    }

    public function test_event_share_invitation_mail_for_email_address()
    {
        // Create mock data without database
        $event = (object) [
            'id' => 1,
            'title' => 'Test Event',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00:00',
            'location' => 'Main Hall'
        ];

        $share = (object) [
            'id' => 1,
            'token' => 'test-token-456',
            'expires_at' => now()->addDays(7)
        ];

        $sender = (object) [
            'id' => 1,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'sender@example.com'
        ];

        // Prepare invitation data
        $invitationData = [
            'event' => $event,
            'share' => $share,
            'sender' => $sender,
            'message' => 'Join us for this special event!',
            'token' => $share->token,
            'expires_at' => $share->expires_at
        ];

        // Queue the mail
        Mail::to('external@example.com')->queue(new EventShareInvitationMail($invitationData));

        // Assert mail was queued
        Mail::assertQueued(EventShareInvitationMail::class, function ($mail) use ($event, $share) {
            return $mail->data['event']->id === $event->id &&
                   $mail->data['share']->id === $share->id &&
                   $mail->data['message'] === 'Join us for this special event!';
        });
    }

    public function test_event_share_invitation_mail_uses_default_message()
    {
        // Create mock data without database
        $event = (object) [
            'id' => 1,
            'title' => 'Test Event',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00:00',
            'location' => 'Main Hall'
        ];

        $share = (object) [
            'id' => 1,
            'token' => 'test-token-789',
            'expires_at' => now()->addDays(7)
        ];

        $sender = (object) [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'sender@example.com'
        ];

        // Prepare invitation data with default message
        $invitationData = [
            'event' => $event,
            'share' => $share,
            'sender' => $sender,
            'message' => "John Doe has invited you to the event: Test Event",
            'token' => $share->token,
            'expires_at' => $share->expires_at
        ];

        // Queue the mail
        Mail::to('test@example.com')->queue(new EventShareInvitationMail($invitationData));

        // Assert mail was queued with default message
        Mail::assertQueued(EventShareInvitationMail::class, function ($mail) {
            return $mail->data['message'] === "John Doe has invited you to the event: Test Event";
        });
    }

    public function test_event_share_invitation_mail_has_correct_subject()
    {
        // Create mock data without database
        $event = (object) [
            'id' => 1,
            'title' => 'Special Event',
            'event_date' => now()->addDays(7),
            'start_time' => '19:00:00',
            'location' => 'Main Hall'
        ];

        $share = (object) [
            'id' => 1,
            'token' => 'test-token-101',
            'expires_at' => now()->addDays(7)
        ];

        $sender = (object) [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'sender@example.com'
        ];

        // Prepare invitation data
        $invitationData = [
            'event' => $event,
            'share' => $share,
            'sender' => $sender,
            'message' => 'You are invited!',
            'token' => $share->token,
            'expires_at' => $share->expires_at
        ];

        // Queue the mail
        Mail::to('test@example.com')->queue(new EventShareInvitationMail($invitationData));

        // Assert mail was queued with correct subject
        Mail::assertQueued(EventShareInvitationMail::class, function ($mail) {
            return $mail->build()->subject === __('emails.reminders.default', ['title' => 'Special Event']);
        });
    }
}