<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DonationReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Donation $donation;
    public bool $attachPdf;
    public ?string $attachmentPath;

    public function __construct(Donation $donation, bool $attachPdf = true, ?string $attachmentPath = null)
    {
        $this->donation = $donation;
        $this->attachPdf = $attachPdf;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $subject = __('emails.donations.thank_you_title') . ' #' . ($this->donation->receipt_number ?? $this->donation->id);
        $mailable = $this->subject($subject)
            ->view('emails.donation-receipt')
            ->with(['donation' => $this->donation]);

        if ($this->attachPdf) {
            $filename = 'Donation-Receipt-' . (($this->donation->receipt_number) ?: $this->donation->id) . '.pdf';

            if ($this->attachmentPath && Storage::disk('public')->exists($this->attachmentPath)) {
                $mailable->attach(Storage::disk('public')->path($this->attachmentPath), [
                    'as' => $filename,
                    'mime' => 'application/pdf',
                ]);
            } else {
                $pdf = Pdf::loadView('tax_receipts.single', [
                    'donation' => $this->donation,
                    'receipt' => (object) [
                        'receipt_number' => $this->donation->receipt_number ?? ('REC-' . $this->donation->id),
                        'issue_date' => now(),
                        'tax_year' => optional($this->donation->donation_date)->format('Y'),
                    ],
                    'member' => $this->donation->member,
                    'organization' => (object) [
                        'name' => config('app.organization_name', config('app.name')),
                        'address' => config('app.organization_address', ''),
                        'phone' => config('app.organization_phone', ''),
                        'email' => config('mail.from.address'),
                        'website' => config('app.url'),
                        'tax_id' => config('app.organization_tax_id', ''),
                        'logo_path' => config('app.organization_logo', 'images/logo.png'),
                    ],
                ]);
                $mailable->attachData($pdf->output(), $filename, ['mime' => 'application/pdf']);
            }
        }

        return $mailable;
    }
}


