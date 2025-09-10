<?php

namespace App\Console\Commands;

use App\Services\Interfaces\GroupAnalyticsServiceInterface;
use Illuminate\Console\Command;

class GenerateGroupAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groups:generate-analytics {--date= : The date to generate analytics for (format: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate analytics for all active groups';

    /**
     * @var GroupAnalyticsServiceInterface
     */
    protected $groupAnalyticsService;

    /**
     * Create a new command instance.
     *
     * @param GroupAnalyticsServiceInterface $groupAnalyticsService
     * @return void
     */
    public function __construct(GroupAnalyticsServiceInterface $groupAnalyticsService)
    {
        parent::__construct();
        $this->groupAnalyticsService = $groupAnalyticsService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->option('date') ? $this->option('date') : now()->format('Y-m-d');

        $this->info("Generating analytics for all active groups for date: {$date}");

        try {
            $count = $this->groupAnalyticsService->scheduleAnalyticsGeneration($date);
            $this->info("Successfully generated analytics for {$count} groups");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating analytics: " . $e->getMessage());
            return 1;
        }
    }
}
