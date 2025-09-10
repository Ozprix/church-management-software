<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DonationControllerSendReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_receipt_emails_donor_and_optionally_gift_recipient(): void
    {
        Mail::fake();

        $donor = Member::factory()->create(['email' => 'donor@example.com']);
        $recipient = Member::factory()->create(['email' => 'recipient@example.com']);
        $donation = Donation::factory()->create([
            'member_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'payment_status' => 'completed',
        ]);

        $this->actingAs(\App\Models\User::factory()->create());

        $response = $this->postJson('/api/donations/' . $donation->id . '/send-receipt', [
            'send_to_gift_recipient' => true,
        ]);

        $response->assertStatus(200);

        Mail::assertQueued(\App\Mail\DonationReceiptMail::class, function ($mail) {
            return $mail->hasTo('donor@example.com');
        });

        Mail::assertQueued(\App\Mail\DonationReceiptMail::class, function ($mail) {
            return $mail->hasTo('recipient@example.com');
        });
    }
}


