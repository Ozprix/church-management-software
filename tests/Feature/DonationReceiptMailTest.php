<?php

namespace Tests\Feature;

use App\Mail\DonationReceiptMail;
use App\Models\Donation;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DonationReceiptMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_receipt_mail_is_queued_with_pdf_attachment(): void
    {
        Mail::fake();

        $member = Member::factory()->create(['email' => 'john@example.com']);
        $donation = Donation::factory()->for($member, 'member')->create([
            'receipt_number' => 'REC-TEST-123',
        ]);

        Mail::to($member->email)->queue(new DonationReceiptMail($donation, attachPdf: false));

        Mail::assertQueued(DonationReceiptMail::class, function ($mail) use ($donation) {
            return $mail->hasTo('john@example.com')
                && $mail->donation->is($donation);
        });
    }
}
