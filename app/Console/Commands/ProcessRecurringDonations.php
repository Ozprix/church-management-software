<?php

namespace App\Console\Commands;

use App\Services\RecurringDonationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringDonations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donations:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all due recurring donations';

    /**
     * The recurring donation service.
     *
     * @var \App\Services\RecurringDonationService
     */
    protected $recurringDonationService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\RecurringDonationService $recurringDonationService
     * @return void
     */
    public function __construct(RecurringDonationService $recurringDonationService)
    {
        parent::__construct();
        $this->recurringDonationService = $recurringDonationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to process recurring donations...');
        
        try {
            $results = $this->recurringDonationService->processDueRecurringDonations();
            
            $this->info("Processed {$results['total']} recurring donations:");
            $this->info("- {$results['success']} successful");
            $this->info("- {$results['failed']} failed");
            $this->info("- {$results['skipped']} skipped");
            
            if ($results['failed'] > 0) {
                $this->warn('Some recurring donations failed to process. Check the logs for details.');
                
                // Log the details of failed donations
                foreach ($results['details'] as $detail) {
                    if ($detail['status'] === 'failed') {
                        Log::error('Failed to process recurring donation', [
                            'recurring_donation_id' => $detail['recurring_donation_id'],
                            'error' => $detail['error'] ?? $detail['message'] ?? 'Unknown error'
                        ]);
                    }
                }
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error processing recurring donations: ' . $e->getMessage());
            Log::error('Error in ProcessRecurringDonations command', [
                'exception' => $e
            ]);
            
            return Command::FAILURE;
        }
    }
}
