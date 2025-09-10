<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Expense;
use App\Models\Project;
use App\Exports\DonationsExport;
use App\Exports\ExpensesExport;
use App\Exports\FinancialSummaryExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class FinancialReportController extends Controller
{
    /**
     * Get financial summary data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSummary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:donation_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date')) 
                : Carbon::now()->startOfMonth()->subMonth();
                
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date')) 
                : Carbon::now();
                
            $categoryId = $request->input('category_id');
            
            // Build donation query with date range
            $donationQuery = Donation::whereBetween('donation_date', [$startDate, $endDate])
                ->where('payment_status', 'completed');
                
            // Add category filter if provided
            if ($categoryId) {
                $donationQuery->where('category_id', $categoryId);
            }
            
            // Get total donations
            $totalDonations = $donationQuery->sum('amount');
            
            // Get total expenses for the same period
            $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount');
                
            // Calculate net income
            $netIncome = $totalDonations - $totalExpenses;
            
            // Calculate average donation
            $donationCount = $donationQuery->count();
            $averageDonation = $donationCount > 0 ? $totalDonations / $donationCount : 0;
            
            // Calculate net income percentage change from previous period
            $previousPeriodLength = $endDate->diffInDays($startDate) + 1;
            $previousPeriodStart = (clone $startDate)->subDays($previousPeriodLength);
            $previousPeriodEnd = (clone $startDate)->subDay();
            
            $previousDonations = Donation::whereBetween('donation_date', [$previousPeriodStart, $previousPeriodEnd])
                ->where('payment_status', 'completed');
                
            if ($categoryId) {
                $previousDonations->where('category_id', $categoryId);
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
            
            return response()->json([
                'totalDonations' => round($totalDonations, 2),
                'totalExpenses' => round($totalExpenses, 2),
                'netIncome' => round($netIncome, 2),
                'netIncomePercentage' => $netIncomePercentage,
                'averageDonation' => round($averageDonation, 2),
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get financial summary: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get chart data for financial dashboard
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:donation_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startDate = $request->input('start_date') 
                ? Carbon::parse($request->input('start_date')) 
                : Carbon::now()->startOfMonth()->subMonth();
                
            $endDate = $request->input('end_date') 
                ? Carbon::parse($request->input('end_date')) 
                : Carbon::now();
                
            $categoryId = $request->input('category_id');
            
            // Get donations by category
            $categoryQuery = DonationCategory::select('donation_categories.id', 'donation_categories.name', DB::raw('SUM(donations.amount) as total'))
                ->leftJoin('donations', 'donation_categories.id', '=', 'donations.category_id')
                ->where('donations.payment_status', 'completed')
                ->whereBetween('donations.donation_date', [$startDate, $endDate])
                ->groupBy('donation_categories.id', 'donation_categories.name')
                ->orderBy('total', 'desc');
                
            if ($categoryId) {
                $categoryQuery->where('donation_categories.id', $categoryId);
            }
            
            $categoriesData = $categoryQuery->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'total' => round($item->total, 2),
                ];
            });
            
            // Get monthly donations and expenses
            $months = [];
            $period = CarbonPeriod::create($startDate->startOfMonth(), '1 month', $endDate->endOfMonth());
            
            foreach ($period as $date) {
                $monthStart = $date->format('Y-m-d');
                $monthEnd = $date->copy()->endOfMonth()->format('Y-m-d');
                $monthLabel = $date->format('M Y');
                
                // Get donations for this month
                $donationQuery = Donation::whereBetween('donation_date', [$monthStart, $monthEnd])
                    ->where('payment_status', 'completed');
                    
                if ($categoryId) {
                    $donationQuery->where('category_id', $categoryId);
                }
                
                $monthlyDonations = $donationQuery->sum('amount');
                
                // Get expenses for this month
                $monthlyExpenses = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                    ->sum('amount');
                
                $months[] = [
                    'month' => $monthLabel,
                    'donations' => round($monthlyDonations, 2),
                    'expenses' => round($monthlyExpenses, 2),
                ];
            }
            
            return response()->json([
                'categories' => $categoriesData,
                'monthly' => $months,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get chart data: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate a PDF report
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Response
     */
    public function generatePdfReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:donation_categories,id',
            'report_type' => 'required|in:summary,detailed,project',
            'project_id' => 'required_if:report_type,project|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            $categoryId = $request->input('category_id');
            $reportType = $request->input('report_type');
            $projectId = $request->input('project_id');
            
            $data = [
                'title' => 'Financial Report',
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
                'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
            
            // Get data based on report type
            switch ($reportType) {
                case 'summary':
                    $data = array_merge($data, $this->getSummaryReportData($startDate, $endDate, $categoryId));
                    $view = 'reports.finance.summary';
                    break;
                case 'detailed':
                    $data = array_merge($data, $this->getDetailedReportData($startDate, $endDate, $categoryId));
                    $view = 'reports.finance.detailed';
                    break;
                case 'project':
                    $data = array_merge($data, $this->getProjectReportData($startDate, $endDate, $projectId));
                    $view = 'reports.finance.project';
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid report type',
                    ], 400);
            }
            
            // Generate PDF
            $pdf = PDF::loadView($view, $data);
            
            return $pdf->download('financial_report_' . $reportType . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate PDF report: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Export financial data to Excel
     *
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:donation_categories,id',
            'report_type' => 'required|in:donations,expenses,summary',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            $categoryId = $request->input('category_id');
            $reportType = $request->input('report_type');
            
            // Generate Excel file based on report type
            switch ($reportType) {
                case 'donations':
                    $export = new DonationsExport($startDate, $endDate, $categoryId);
                    $filename = 'donations_' . Carbon::now()->format('Y-m-d') . '.xlsx';
                    break;
                case 'expenses':
                    $export = new ExpensesExport($startDate, $endDate);
                    $filename = 'expenses_' . Carbon::now()->format('Y-m-d') . '.xlsx';
                    break;
                case 'summary':
                    $export = new FinancialSummaryExport($startDate, $endDate, $categoryId);
                    $filename = 'financial_summary_' . Carbon::now()->format('Y-m-d') . '.xlsx';
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid report type',
                    ], 400);
            }
            
            // Return Excel download response
            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to export to Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get data for summary report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $categoryId
     * @return array
     */
    private function getSummaryReportData(Carbon $startDate, Carbon $endDate, ?int $categoryId): array
    {
        // Build donation query with date range
        $donationQuery = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->where('payment_status', 'completed');
            
        // Add category filter if provided
        if ($categoryId) {
            $donationQuery->where('category_id', $categoryId);
        }
        
        // Get total donations
        $totalDonations = $donationQuery->sum('amount');
        
        // Get total expenses for the same period
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
            
        // Calculate net income
        $netIncome = $totalDonations - $totalExpenses;
        
        // Get donations by category
        $donationsByCategory = DonationCategory::select('donation_categories.id', 'donation_categories.name', DB::raw('SUM(donations.amount) as total'))
            ->leftJoin('donations', 'donation_categories.id', '=', 'donations.category_id')
            ->where('donations.payment_status', 'completed')
            ->whereBetween('donations.donation_date', [$startDate, $endDate])
            ->groupBy('donation_categories.id', 'donation_categories.name')
            ->orderBy('total', 'desc')
            ->get();
            
        // Get project funding status
        $projects = Project::select('id', 'name', 'goal_amount', 'current_amount', 'status')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get()
            ->map(function ($project) {
                $percentComplete = $project->goal_amount > 0 
                    ? round(($project->current_amount / $project->goal_amount) * 100) 
                    : 0;
                    
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'goal_amount' => $project->goal_amount,
                    'current_amount' => $project->current_amount,
                    'percent_complete' => $percentComplete,
                    'status' => $project->status,
                ];
            });
            
        return [
            'total_donations' => round($totalDonations, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_income' => round($netIncome, 2),
            'donations_by_category' => $donationsByCategory,
            'projects' => $projects,
        ];
    }
    
    /**
     * Get data for detailed report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $categoryId
     * @return array
     */
    private function getDetailedReportData(Carbon $startDate, Carbon $endDate, ?int $categoryId): array
    {
        // Build donation query with date range
        $donationQuery = Donation::with(['member', 'category', 'project'])
            ->whereBetween('donation_date', [$startDate, $endDate])
            ->where('payment_status', 'completed');
            
        // Add category filter if provided
        if ($categoryId) {
            $donationQuery->where('category_id', $categoryId);
        }
        
        // Get donations
        $donations = $donationQuery->orderBy('donation_date', 'desc')->get();
        
        // Get expenses
        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();
            
        return [
            'donations' => $donations,
            'expenses' => $expenses,
            'total_donations' => round($donations->sum('amount'), 2),
            'total_expenses' => round($expenses->sum('amount'), 2),
            'net_income' => round($donations->sum('amount') - $expenses->sum('amount'), 2),
        ];
    }
    
    /**
     * Get data for project report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $projectId
     * @return array
     */
    private function getProjectReportData(Carbon $startDate, Carbon $endDate, int $projectId): array
    {
        // Get project
        $project = Project::findOrFail($projectId);
        
        // Get donations for this project
        $donations = Donation::with(['member'])
            ->whereBetween('donation_date', [$startDate, $endDate])
            ->where('project_id', $projectId)
            ->where('payment_status', 'completed')
            ->orderBy('donation_date', 'desc')
            ->get();
            
        // Calculate percentage complete
        $percentComplete = $project->goal_amount > 0 
            ? round(($project->current_amount / $project->goal_amount) * 100) 
            : 0;
            
        return [
            'project' => $project,
            'donations' => $donations,
            'total_donations' => round($donations->sum('amount'), 2),
            'percent_complete' => $percentComplete,
            'remaining_amount' => round($project->goal_amount - $project->current_amount, 2),
        ];
    }
}
