<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnniversaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $member;
    protected $daysUntil;
    protected $years;

    /**
     * Create a new notification instance.
     *
     * @param Member $member
     * @param int $daysUntil
     * @param int $years
     * @return void
     */
    public function __construct(Member $member, int $daysUntil, int $years)
    {
        $this->member = $member;
        $this->daysUntil = $daysUntil;
        $this->years = $years;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->daysUntil === 0
            ? "💍 Happy {$this->years}th Anniversary to {$this->member->first_name} {$this->member->last_name}!"
            : "💍 Upcoming Anniversary: {$this->member->first_name} {$this->member->last_name} in {$this->daysUntil} days";

        $mailMessage = new MailMessage;
        $mailMessage->subject($subject);
        
        if ($this->daysUntil === 0) {
            $mailMessage->line("Today is {$this->member->first_name} {$this->member->last_name}'s {$this->years}th wedding anniversary!")
                ->line("Don't forget to congratulate them on this special day!");
        } else {
            $anniversaryDate = $this->member->anniversary_date->format('F jS');
            $mailMessage->line("{$this->member->first_name} {$this->member->last_name}'s {$this->years}th wedding anniversary is coming up in {$this->daysUntil} days on {$anniversaryDate}.")
                ->line("Make sure to prepare for this special occasion!");
        }
        
        $mailMessage->line("Contact Information:")
            ->line("Email: {$this->member->email}")
            ->line("Phone: {$this->member->phone}")
            ->action('View Member Profile', url("/members/{$this->member->id}"));
            
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'member_id' => $this->member->id,
            'member_name' => $this->member->full_name,
            'event_type' => 'anniversary',
            'days_until' => $this->daysUntil,
            'years' => $this->years,
            'date' => $this->member->anniversary_date->format('Y-m-d'),
        ];
    }
}
