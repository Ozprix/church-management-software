<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventShareInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $event = $this->data['event'];
        $subject = __('emails.reminders.default', ['title' => ($event->title ?? __('emails.reminders.untitled'))]);

        return $this->subject($subject)
            ->view('emails.event-share-invitation')
            ->with($this->data);
    }
}



