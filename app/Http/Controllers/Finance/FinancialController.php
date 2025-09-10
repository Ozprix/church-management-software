<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\Member;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialController extends Controller
{
    /**
     * Get financial summary data for the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        // Set default date range to current year if not provided
        $startDate = $request->start_date ?? date('Y-01-01');
        $endDate = $request->end_date ?? date('Y-12-31');
        
        // Get total income (donations)
        $totalIncome = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Get total expenses
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Calculate net income
        $netIncome = $totalIncome - $totalExpenses;
        
        // Get total number of unique donors
        $totalDonors = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->distinct('member_id')
            ->count('member_id');
        
        // Get income by category (campaigns)
        $incomeByCategory = DB::table('donations')
            ->leftJoin('campaigns', 'donations.campaign_id', '=', 'campaigns.id')
            ->select(
                DB::raw('COALESCE(campaigns.name, "General Fund") as category'),
                DB::raw('SUM(donations.amount) as amount')
            )
            ->whereBetween('donations.donation_date', [$startDate, $endDate])
            ->groupBy('category')
            ->orderBy('amount', 'desc')
            ->get();
        
        // Get expenses by category
        $expensesByCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('SUM(amount) as amount'))
            ->groupBy('category')
            ->orderBy('amount', 'desc')
            ->get();
        
        // Get monthly data for trend chart
        $monthlyData = $this->getMonthlyData($startDate, $endDate);
        
        // Get top donors
        $topDonors = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select(
                'member_id',
                DB::raw('SUM(amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->with('member:id,first_name,last_name')
            ->groupBy('member_id')
            ->orderBy('amount', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($donation) {
                return [
                    'name' => $donation->member ? $donation->member->first_name . ' ' . $donation->member->last_name : 'Unknown',
                    'amount' => $donation->amount,
                    'count' => $donation->count
                ];
            });
        
        // Get recent expenses
        $recentExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('title', 'category', 'amount', 'expense_date as date')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                    'total_donors' => $totalDonors
                ],
                'income_by_category' => $incomeByCategory,
                'expenses_by_category' => $expensesByCategory,
                'monthly_data' => $monthlyData,
                'top_donors' => $topDonors,
                'recent_expenses' => $recentExpenses,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }
    
    /**
     * Get monthly income and expense data for trend chart.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    private function getMonthlyData($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();
        
        $months = [];
        $current = $start->copy();
        
        // Generate array of all months in the range
        while ($current->lte($end)) {
            $yearMonth = $current->format('Y-m');
            $months[$yearMonth] = [
                'year' => $current->year,
                'month' => $current->month,
                'income' => 0,
                'expenses' => 0
            ];
            $current->addMonth();
        }
        
        // Get monthly income data
        $monthlyIncome = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(donation_date) as year'),
                DB::raw('MONTH(donation_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month')
            ->get();
        
        // Get monthly expense data
        $monthlyExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('year', 'month')
            ->get();
        
        // Populate income data
        foreach ($monthlyIncome as $item) {
            $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            if (isset($months[$key])) {
                $months[$key]['income'] = $item->total;
            }
        }
        
        // Populate expense data
        foreach ($monthlyExpenses as $item) {
            $key = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            if (isset($months[$key])) {
                $months[$key]['expenses'] = $item->total;
            }
        }
        
        // Convert associative array to indexed array
        return array_values($months);
    }
    
    /**
     * Get budget utilization data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function budgetUtilization(Request $request)
    {
        // Set default date range to current year if not provided
        $startDate = $request->start_date ?? date('Y-01-01');
        $endDate = $request->end_date ?? date('Y-12-31');
        
        // Get active budgets that overlap with the date range
        $budgets = Budget::whereRaw('? BETWEEN start_date AND end_date OR ? BETWEEN start_date AND end_date', [$startDate, $endDate])
            ->orWhereRaw('start_date BETWEEN ? AND ?', [$startDate, $endDate])
            ->orWhereRaw('end_date BETWEEN ? AND ?', [$startDate, $endDate])
            ->select('id', 'name', 'amount', 'spent_amount', 'start_date', 'end_date')
            ->get()
            ->map(function ($budget) {
                $budget->utilization_percentage = $budget->amount > 0 ? ($budget->spent_amount / $budget->amount) * 100 : 0;
                $budget->remaining = $budget->amount - $budget->spent_amount;
                return $budget;
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $budgets
        ]);
    }
    
    /**
     * Get campaign progress data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function campaignProgress(Request $request)
    {
        // Get active campaigns
        $campaigns = Campaign::where('end_date', '>=', now())
            ->select('id', 'name', 'target_amount', 'raised_amount', 'start_date', 'end_date')
            ->get()
            ->map(function ($campaign) {
                $campaign->progress_percentage = $campaign->target_amount > 0 ? ($campaign->raised_amount / $campaign->target_amount) * 100 : 0;
                $campaign->remaining = $campaign->target_amount - $campaign->raised_amount;
                
                // Calculate days remaining
                $endDate = Carbon::parse($campaign->end_date);
                $campaign->days_remaining = now()->diffInDays($endDate, false);
                
                return $campaign;
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $campaigns
        ]);
    }
}
