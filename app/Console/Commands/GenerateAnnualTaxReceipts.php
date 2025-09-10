<?php

namespace App\Console\Commands;

use App\Services\TaxReceiptService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateAnnualTaxReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax-receipts:generate-annual {year? : The tax year to generate receipts for (defaults to previous year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate annual tax receipts for all eligible members';

    /**
     * The tax receipt service.
     *
     * @var \App\Services\TaxReceiptService
     */
    protected $taxReceiptService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\TaxReceiptService $taxReceiptService
     * @return void
     */
    public function __construct(TaxReceiptService $taxReceiptService)
    {
        parent::__construct();
        $this->taxReceiptService = $taxReceiptService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the year from argument or use previous year
        $year = $this->argument('year') ?? Carbon::now()->subYear()->year;
        
        $this->info("Starting to generate annual tax receipts for {$year} tax year...");
        
        try {
            $results = $this->taxReceiptService->generateAllAnnualReceipts($year);
            
            $this->info("Processed tax receipts for {$results['total']} members:");
            $this->info("- {$results['success']} successful");
            $this->info("- {$results['failed']} failed");
            
            if ($results['failed'] > 0) {
                $this->warn('Some tax receipts failed to generate. Check the logs for details.');
                
                // Log the details of failed receipts
                foreach ($results['details'] as $detail) {
                    if ($detail['status'] === 'failed') {
                        Log::error('Failed to generate annual tax receipt', [
                            'member_id' => $detail['member_id'],
                            'member_name' => $detail['member_name'],
                            'reason' => $detail['reason'] ?? 'Unknown error'
                        ]);
                    }
                }
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error generating annual tax receipts: ' . $e->getMessage());
            Log::error('Error in GenerateAnnualTaxReceipts command', [
                'exception' => $e
            ]);
            
            return Command::FAILURE;
        }
    }
}
