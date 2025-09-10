<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use App\Notifications\BirthdayNotification;
use App\Notifications\AnniversaryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-date-notifications {--days=7 : Days in advance to send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for upcoming birthdays and anniversaries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendBirthdayNotifications();
        $this->sendAnniversaryNotifications();
        
        $this->info('Date notifications sent successfully.');
    }
    
    /**
     * Send birthday notifications.
     */
    private function sendBirthdayNotifications()
    {
        $today = Carbon::today();
        $daysInAdvance = $this->option('days');
        
        // Get admin users to notify
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        
        if ($admins->isEmpty()) {
            $this->warn('No admin users found to notify about birthdays.');
            return;
        }
        
        // Check for birthdays today
        $this->checkBirthdaysForDate($today, 0, $admins);
        
        // Check for upcoming birthdays
        $futureDate = $today->copy()->addDays($daysInAdvance);
        $this->checkBirthdaysForDate($futureDate, $daysInAdvance, $admins);
    }
    
    /**
     * Check for birthdays on a specific date.
     *
     * @param Carbon $date
     * @param int $daysUntil
     * @param \Illuminate\Support\Collection $admins
     */
    private function checkBirthdaysForDate(Carbon $date, int $daysUntil, $admins)
    {
        $month = $date->format('m');
        $day = $date->format('d');
        
        $members = Member::whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", ["$month-$day"])
            ->where('membership_status', 'active')
            ->get();
            
        $count = $members->count();
        
        if ($count > 0) {
            $this->info("Found $count " . ($daysUntil === 0 ? "birthdays today" : "birthdays in $daysUntil days") . ".");
            
            foreach ($members as $member) {
                foreach ($admins as $admin) {
                    $admin->notify(new BirthdayNotification($member, $daysUntil));
                }
            }
        } else {
            $this->info("No birthdays found " . ($daysUntil === 0 ? "today" : "in $daysUntil days") . ".");
        }
    }
    
    /**
     * Send anniversary notifications.
     */
    private function sendAnniversaryNotifications()
    {
        $today = Carbon::today();
        $daysInAdvance = $this->option('days');
        
        // Get admin users to notify
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        
        if ($admins->isEmpty()) {
            $this->warn('No admin users found to notify about anniversaries.');
            return;
        }
        
        // Check for anniversaries today
        $this->checkAnniversariesForDate($today, 0, $admins);
        
        // Check for upcoming anniversaries
        $futureDate = $today->copy()->addDays($daysInAdvance);
        $this->checkAnniversariesForDate($futureDate, $daysInAdvance, $admins);
    }
    
    /**
     * Check for anniversaries on a specific date.
     *
     * @param Carbon $date
     * @param int $daysUntil
     * @param \Illuminate\Support\Collection $admins
     */
    private function checkAnniversariesForDate(Carbon $date, int $daysUntil, $admins)
    {
        $month = $date->format('m');
        $day = $date->format('d');
        
        $members = Member::whereRaw("DATE_FORMAT(anniversary_date, '%m-%d') = ?", ["$month-$day"])
            ->whereNotNull('anniversary_date')
            ->where('membership_status', 'active')
            ->get();
            
        $count = $members->count();
        
        if ($count > 0) {
            $this->info("Found $count " . ($daysUntil === 0 ? "anniversaries today" : "anniversaries in $daysUntil days") . ".");
            
            foreach ($members as $member) {
                // Calculate years of marriage
                $years = $date->year - $member->anniversary_date->year;
                
                foreach ($admins as $admin) {
                    $admin->notify(new AnniversaryNotification($member, $daysUntil, $years));
                }
            }
        } else {
            $this->info("No anniversaries found " . ($daysUntil === 0 ? "today" : "in $daysUntil days") . ".");
        }
    }
}
