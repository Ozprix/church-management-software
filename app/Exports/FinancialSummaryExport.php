<?php

namespace App\Exports;

use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Expense;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialSummaryExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $categoryId
     */
    public function __construct(Carbon $startDate, Carbon $endDate, ?int $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
        // Summary sheet
        $sheets[] = new FinancialOverviewSheet($this->startDate, $this->endDate, $this->categoryId);
        
        // Donations by category sheet
        $sheets[] = new DonationsByCategorySheet($this->startDate, $this->endDate, $this->categoryId);
        
        // Projects sheet
        $sheets[] = new ProjectsSheet();
        
        // Monthly breakdown sheet
        $sheets[] = new MonthlyBreakdownSheet($this->startDate, $this->endDate, $this->categoryId);
        
        // Detailed donations sheet
        $sheets[] = new DonationsExport($this->startDate, $this->endDate, $this->categoryId);
        
        // Detailed expenses sheet
        $sheets[] = new ExpensesExport($this->startDate, $this->endDate);
        
        return $sheets;
    }
}

class FinancialOverviewSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    public function __construct(Carbon $startDate, Carbon $endDate, ?int $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        // Build donation query with date range
        $donationQuery = Donation::whereBetween('donation_date', [$this->startDate, $this->endDate])
            ->where('payment_status', 'completed');
            
        // Add category filter if provided
        if ($this->categoryId) {
            $donationQuery->where('category_id', $this->categoryId);
        }
        
        // Get total donations
        $totalDonations = $donationQuery->sum('amount');
        
        // Get total expenses for the same period
        $totalExpenses = Expense::whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->sum('amount');
            
        // Calculate net income
        $netIncome = $totalDonations - $totalExpenses;
        
        // Calculate average donation
        $donationCount = $donationQuery->count();
        $averageDonation = $donationCount > 0 ? $totalDonations / $donationCount : 0;
        
        // Calculate net income percentage change from previous period
        $previousPeriodLength = $this->endDate->diffInDays($this->startDate) + 1;
        $previousPeriodStart = (clone $this->startDate)->subDays($previousPeriodLength);
        $previousPeriodEnd = (clone $this->startDate)->subDay();
        
        $previousDonations = Donation::whereBetween('donation_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'completed');
            
        if ($this->categoryId) {
            $previousDonations->where('category_id', $this->categoryId);
        }
        
        $previousTotalDonations = $previousDonations->sum('amount');
        
        $previousTotalExpenses = Expense::whereBetween('expense_date', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('amount');
            
        $previousNetIncome = $previousTotalDonations - $previousTotalExpenses;
        
        $netIncomePercentage = 0;
        if ($previousNetIncome != 0) {
            $netIncomePercentage = round((($netIncome - $previousNetIncome) / abs($previousNetIncome)) * 100, 2);
        } elseif ($netIncome > 0) {
            $netIncomePercentage = 100;
        }
        
        return collect([
            [
                'Metric', 'Value', 'Previous Period', 'Change (%)'
            ],
            [
                'Total Donations', $totalDonations, $previousTotalDonations, 
                $previousTotalDonations > 0 ? round((($totalDonations - $previousTotalDonations) / $previousTotalDonations) * 100, 2) : 0
            ],
            [
                'Total Expenses', $totalExpenses, $previousTotalExpenses, 
                $previousTotalExpenses > 0 ? round((($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100, 2) : 0
            ],
            [
                'Net Income', $netIncome, $previousNetIncome, $netIncomePercentage
            ],
            [
                'Average Donation', $averageDonation, 
                $previousDonations->count() > 0 ? $previousTotalDonations / $previousDonations->count() : 0,
                0
            ],
            [
                'Number of Donations', $donationCount, $previousDonations->count(),
                $previousDonations->count() > 0 ? round((($donationCount - $previousDonations->count()) / $previousDonations->count()) * 100, 2) : 0
            ],
            [
                'Period', $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d'),
                $previousPeriodStart->format('Y-m-d') . ' to ' . $previousPeriodEnd->format('Y-m-d'),
                ''
            ]
        ]);
    }

    public function title(): string
    {
        return 'Financial Overview';
    }

    public function headings(): array
    {
        // Headings are included in the collection
        return [];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'B' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'C' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
        ];
    }
}

class DonationsByCategorySheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    public function __construct(Carbon $startDate, Carbon $endDate, ?int $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $query = DonationCategory::select('donation_categories.id', 'donation_categories.name', DB::raw('SUM(donations.amount) as total'))
            ->leftJoin('donations', 'donation_categories.id', '=', 'donations.category_id')
            ->where('donations.payment_status', 'completed')
            ->whereBetween('donations.donation_date', [$this->startDate, $this->endDate])
            ->groupBy('donation_categories.id', 'donation_categories.name')
            ->orderBy('total', 'desc');
            
        if ($this->categoryId) {
            $query->where('donation_categories.id', $this->categoryId);
        }
        
        $categories = $query->get();
        
        $totalDonations = $categories->sum('total');
        
        $data = collect([
            ['Category ID', 'Category Name', 'Amount', '% of Total']
        ]);
        
        foreach ($categories as $category) {
            $percentage = $totalDonations > 0 ? ($category->total / $totalDonations) * 100 : 0;
            
            $data->push([
                $category->id,
                $category->name,
                $category->total,
                round($percentage, 2)
            ]);
        }
        
        $data->push([
            '',
            'Total',
            $totalDonations,
            100
        ]);
        
        return $data;
    }

    public function title(): string
    {
        return 'Donations by Category';
    }

    public function headings(): array
    {
        // Headings are included in the collection
        return [];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'C' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'D' => ['numberFormat' => ['formatCode' => '0.00"%"']],
        ];
    }
}

class ProjectsSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    public function collection()
    {
        $projects = Project::select('id', 'name', 'description', 'start_date', 'end_date', 'goal_amount', 'current_amount', 'status')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get();
            
        $data = collect([
            ['Project ID', 'Name', 'Description', 'Start Date', 'End Date', 'Goal Amount', 'Current Amount', 'Remaining', '% Complete', 'Status']
        ]);
        
        foreach ($projects as $project) {
            $percentComplete = $project->goal_amount > 0 ? round(($project->current_amount / $project->goal_amount) * 100, 2) : 0;
            $remaining = $project->goal_amount - $project->current_amount;
            
            $data->push([
                $project->id,
                $project->name,
                $project->description,
                $project->start_date->format('Y-m-d'),
                $project->end_date ? $project->end_date->format('Y-m-d') : 'Ongoing',
                $project->goal_amount,
                $project->current_amount,
                $remaining,
                $percentComplete,
                ucfirst($project->status)
            ]);
        }
        
        return $data;
    }

    public function title(): string
    {
        return 'Active Projects';
    }

    public function headings(): array
    {
        // Headings are included in the collection
        return [];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'F' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'G' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'H' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'I' => ['numberFormat' => ['formatCode' => '0.00"%"']],
        ];
    }
}

class MonthlyBreakdownSheet implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    public function __construct(Carbon $startDate, Carbon $endDate, ?int $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $data = collect([
            ['Month', 'Donations', 'Expenses', 'Net Income']
        ]);
        
        $period = \Carbon\CarbonPeriod::create($this->startDate->startOfMonth(), '1 month', $this->endDate->endOfMonth());
        
        foreach ($period as $date) {
            $monthStart = $date->format('Y-m-d');
            $monthEnd = $date->copy()->endOfMonth()->format('Y-m-d');
            $monthLabel = $date->format('M Y');
            
            // Get donations for this month
            $donationQuery = Donation::whereBetween('donation_date', [$monthStart, $monthEnd])
                ->where('payment_status', 'completed');
                
            if ($this->categoryId) {
                $donationQuery->where('category_id', $this->categoryId);
            }
            
            $monthlyDonations = $donationQuery->sum('amount');
            
            // Get expenses for this month
            $monthlyExpenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $netIncome = $monthlyDonations - $monthlyExpenses;
            
            $data->push([
                $monthLabel,
                $monthlyDonations,
                $monthlyExpenses,
                $netIncome
            ]);
        }
        
        return $data;
    }

    public function title(): string
    {
        return 'Monthly Breakdown';
    }

    public function headings(): array
    {
        // Headings are included in the collection
        return [];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'B' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'C' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
            'D' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
        ];
    }
}
