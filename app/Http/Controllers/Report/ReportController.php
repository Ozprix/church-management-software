<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Member;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Campaign;
use App\Models\Pledge;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Report::with('creator');
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by favorites
        if ($request->has('favorites') && $request->favorites === 'true') {
            $query->where('is_favorite', true);
        }
        
        // Filter by scheduled reports
        if ($request->has('scheduled') && $request->scheduled === 'true') {
            $query->where('is_scheduled', true);
        }
        
        // Filter by created_by
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }
        
        // Sort by created_at by default, most recent first
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $reports = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    /**
     * Get available report types and parameters.
     *
     * @return \Illuminate\Http\Response
     */
    public function types()
    {
        $types = [
            [
                'id' => 'financial',
                'name' => 'Financial Summary',
                'description' => 'Overall financial health including income, expenses, and net income',
                'parameters' => [
                    'date_range' => 'required',
                    'include_campaigns' => 'optional',
                    'include_pledges' => 'optional'
                ]
            ],
            [
                'id' => 'donation',
                'name' => 'Donation Report',
                'description' => 'Detailed report of all donations with various filtering options',
                'parameters' => [
                    'date_range' => 'required',
                    'payment_method' => 'optional',
                    'campaign_id' => 'optional',
                    'member_id' => 'optional'
                ]
            ],
            [
                'id' => 'expense',
                'name' => 'Expense Report',
                'description' => 'Detailed report of all expenses with categorization',
                'parameters' => [
                    'date_range' => 'required',
                    'category' => 'optional',
                    'budget_id' => 'optional'
                ]
            ],
            [
                'id' => 'attendance',
                'name' => 'Attendance Report',
                'description' => 'Attendance statistics for events and services',
                'parameters' => [
                    'date_range' => 'required',
                    'event_id' => 'optional',
                    'event_type' => 'optional'
                ]
            ],
            [
                'id' => 'membership',
                'name' => 'Membership Report',
                'description' => 'Member statistics, growth, and demographics',
                'parameters' => [
                    'date_range' => 'optional',
                    'status' => 'optional',
                    'demographic_filters' => 'optional'
                ]
            ],
            [
                'id' => 'pledge',
                'name' => 'Pledge Report',
                'description' => 'Status of pledges and fulfillment rates',
                'parameters' => [
                    'date_range' => 'required',
                    'status' => 'optional',
                    'campaign_id' => 'optional'
                ]
            ],
            [
                'id' => 'campaign',
                'name' => 'Campaign Report',
                'description' => 'Progress of fundraising campaigns',
                'parameters' => [
                    'campaign_id' => 'optional',
                    'status' => 'optional'
                ]
            ],
            [
                'id' => 'custom',
                'name' => 'Custom Report',
                'description' => 'Create a custom report with selected fields and filters',
                'parameters' => [
                    'entity_type' => 'required', // members, donations, expenses, etc.
                    'fields' => 'required',
                    'filters' => 'optional',
                    'grouping' => 'optional',
                    'sorting' => 'optional'
                ]
            ]
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $types
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'parameters' => 'nullable|string',
            'filters' => 'nullable|string',
            'output_format' => 'required|in:pdf,csv,excel',
            'is_favorite' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule_frequency' => 'nullable|required_if:is_scheduled,true|in:daily,weekly,monthly,quarterly'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $report = new Report($request->all());
        $report->created_by = Auth::id();
        $report->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Report created successfully',
            'data' => $report
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $report = Report::with('creator')->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    /**
     * Toggle favorite status for a report.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleFavorite(string $id)
    {
        $report = Report::findOrFail($id);
        $report->is_favorite = !$report->is_favorite;
        $report->save();
        
        return response()->json([
            'status' => 'success',
            'message' => $report->is_favorite ? 'Report added to favorites' : 'Report removed from favorites',
            'data' => $report
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'parameters' => 'nullable|string',
            'filters' => 'nullable|string',
            'output_format' => 'sometimes|required|in:pdf,csv,excel',
            'is_favorite' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule_frequency' => 'nullable|required_if:is_scheduled,true|in:daily,weekly,monthly,quarterly'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $report->update($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Report updated successfully',
            'data' => $report
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Report deleted successfully'
        ]);
    }
    
    /**
     * Generate a report based on the specified parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request, string $id)
    {
        $report = Report::findOrFail($id);
        
        // Update last generated timestamp
        $report->last_generated_at = now();
        $report->save();
        
        // Generate report based on type
        switch ($report->type) {
            case 'financial':
                return $this->generateFinancialReport($report, $request);
            case 'donation':
                return $this->generateDonationReport($report, $request);
            case 'expense':
                return $this->generateExpenseReport($report, $request);
            case 'attendance':
                return $this->generateAttendanceReport($report, $request);
            case 'membership':
                return $this->generateMembershipReport($report, $request);
            case 'pledge':
                return $this->generatePledgeReport($report, $request);
            case 'campaign':
                return $this->generateCampaignReport($report, $request);
            case 'custom':
                return $this->generateCustomReport($report, $request);
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unsupported report type'
                ], 400);
        }
    }
    
    /**
     * Generate a financial summary report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateFinancialReport(Report $report, Request $request)
    {
        // Get date range from parameters or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? date('Y-01-01'));
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? date('Y-12-31'));
        
        // Get total income (donations)
        $totalIncome = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Get total expenses
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Calculate net income
        $netIncome = $totalIncome - $totalExpenses;
        
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
        $monthlyData = $this->getMonthlyFinancialData($startDate, $endDate);
        
        // Include campaign data if requested
        $campaignData = [];
        if (isset($parameters['include_campaigns']) && $parameters['include_campaigns']) {
            $campaignData = Campaign::select('id', 'name', 'target_amount', 'raised_amount', 'start_date', 'end_date')
                ->where('end_date', '>=', $startDate)
                ->get()
                ->map(function ($campaign) {
                    $campaign->progress_percentage = $campaign->target_amount > 0 ? 
                        ($campaign->raised_amount / $campaign->target_amount) * 100 : 0;
                    return $campaign;
                });
        }
        
        // Include pledge data if requested
        $pledgeData = [];
        if (isset($parameters['include_pledges']) && $parameters['include_pledges']) {
            $pledgeData = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
                ->select(
                    DB::raw('COUNT(*) as total_pledges'),
                    DB::raw('SUM(amount) as total_pledged'),
                    DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_pledges'),
                    DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_pledges')
                )
                ->first();
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'report_info' => [
                    'name' => $report->name,
                    'type' => $report->type,
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                    'date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ],
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                    'income_by_category' => $incomeByCategory,
                    'expenses_by_category' => $expensesByCategory,
                    'monthly_data' => $monthlyData
                ],
                'campaigns' => $campaignData,
                'pledges' => $pledgeData
            ]
        ]);
    }
    
    /**
     * Generate a donation report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateDonationReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? date('Y-01-01'));
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? date('Y-12-31'));
        $paymentMethod = $request->payment_method ?? ($parameters['payment_method'] ?? null);
        $campaignId = $request->campaign_id ?? ($parameters['campaign_id'] ?? null);
        $memberId = $request->member_id ?? ($parameters['member_id'] ?? null);
        
        // Build query
        $query = Donation::with(['member:id,first_name,last_name', 'campaign:id,name'])
            ->whereBetween('donation_date', [$startDate, $endDate]);
            
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }
        
        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }
        
        if ($memberId) {
            $query->where('member_id', $memberId);
        }
        
        // Get donations
        $donations = $query->orderBy('donation_date', 'desc')->get();
        
        // Calculate summary statistics
        $totalAmount = $donations->sum('amount');
        $averageAmount = $donations->count() > 0 ? $totalAmount / $donations->count() : 0;
        $largestDonation = $donations->max('amount');
        $smallestDonation = $donations->min('amount');
        
        // Group by payment method
        $byPaymentMethod = $donations->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            });
        
        // Group by campaign
        $byCampaign = $donations->groupBy(function ($donation) {
                return $donation->campaign ? $donation->campaign->name : 'General Fund';
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            });
        
        // Group by month
        $byMonth = $donations->groupBy(function ($donation) {
                return Carbon::parse($donation->donation_date)->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount')
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'report_info' => [
                    'name' => $report->name,
                    'type' => $report->type,
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                    'date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'filters' => [
                        'payment_method' => $paymentMethod,
                        'campaign_id' => $campaignId,
                        'member_id' => $memberId
                    ]
                ],
                'summary' => [
                    'total_donations' => $donations->count(),
                    'total_amount' => $totalAmount,
                    'average_amount' => $averageAmount,
                    'largest_donation' => $largestDonation,
                    'smallest_donation' => $smallestDonation
                ],
                'by_payment_method' => $byPaymentMethod,
                'by_campaign' => $byCampaign,
                'by_month' => $byMonth,
                'donations' => $donations
            ]
        ]);
    }
    
    /**
     * Get monthly financial data for trend chart.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    private function getMonthlyFinancialData($startDate, $endDate)
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
                'month_name' => $current->format('M Y'),
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
     * Generate an expense report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateExpenseReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? date('Y-01-01'));
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? date('Y-12-31'));
        $category = $request->category ?? ($parameters['category'] ?? null);
        $budgetId = $request->budget_id ?? ($parameters['budget_id'] ?? null);
        
        // Implementation similar to donation report but for expenses
        // This is a placeholder - the full implementation would be similar to the donation report
        return response()->json([
            'status' => 'success',
            'message' => 'Expense report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Generate an attendance report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateAttendanceReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? date('Y-01-01'));
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? date('Y-12-31'));
        $eventId = $request->event_id ?? ($parameters['event_id'] ?? null);
        $eventType = $request->event_type ?? ($parameters['event_type'] ?? null);
        
        // Implementation for attendance report
        // This is a placeholder - the full implementation would analyze attendance data
        return response()->json([
            'status' => 'success',
            'message' => 'Attendance report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Generate a membership report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateMembershipReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? null);
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? null);
        $status = $request->status ?? ($parameters['status'] ?? null);
        $demographicFilters = $request->demographic_filters ?? ($parameters['demographic_filters'] ?? null);
        
        // Implementation for membership report
        // This is a placeholder - the full implementation would analyze membership data
        return response()->json([
            'status' => 'success',
            'message' => 'Membership report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Generate a pledge report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generatePledgeReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $startDate = $request->start_date ?? ($parameters['start_date'] ?? date('Y-01-01'));
        $endDate = $request->end_date ?? ($parameters['end_date'] ?? date('Y-12-31'));
        $status = $request->status ?? ($parameters['status'] ?? null);
        $campaignId = $request->campaign_id ?? ($parameters['campaign_id'] ?? null);
        
        // Implementation for pledge report
        // This is a placeholder - the full implementation would analyze pledge data
        return response()->json([
            'status' => 'success',
            'message' => 'Pledge report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Generate a campaign report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateCampaignReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $campaignId = $request->campaign_id ?? ($parameters['campaign_id'] ?? null);
        $status = $request->status ?? ($parameters['status'] ?? null);
        
        // Implementation for campaign report
        // This is a placeholder - the full implementation would analyze campaign data
        return response()->json([
            'status' => 'success',
            'message' => 'Campaign report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Generate a custom report.
     *
     * @param  \App\Models\Report  $report
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function generateCustomReport(Report $report, Request $request)
    {
        // Get parameters from report or request
        $parameters = [];
        if (!empty($report->parameters)) {
            try {
                $parameters = json_decode($report->parameters, true) ?? [];
            } catch (\Exception $e) {
                // Handle JSON decode error
                $parameters = [];
            }
        }
        
        $entityType = $request->entity_type ?? ($parameters['entity_type'] ?? null);
        $fields = $request->fields ?? ($parameters['fields'] ?? []);
        $filters = $request->filters ?? ($parameters['filters'] ?? []);
        $grouping = $request->grouping ?? ($parameters['grouping'] ?? null);
        $sorting = $request->sorting ?? ($parameters['sorting'] ?? null);
        
        // Implementation for custom report
        // This is a placeholder - the full implementation would build a dynamic query based on parameters
        return response()->json([
            'status' => 'success',
            'message' => 'Custom report generation will be implemented in the next phase'
        ]);
    }
    
    /**
     * Get dashboard metrics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function metrics(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date range',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Set default date range if not provided
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $endDate->copy()->subMonth();
        
        // Calculate previous period for trend comparison
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($periodDays);
        $prevEndDate = $startDate->copy()->subDay();
        
        // Get total donations for current period
        $totalDonations = Donation::whereBetween('donation_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('amount');
            
        // Get total donations for previous period
        $prevTotalDonations = Donation::whereBetween('donation_date', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])
            ->sum('amount');
        
        // Calculate donation trend percentage
        $donationTrend = $prevTotalDonations > 0 
            ? round((($totalDonations - $prevTotalDonations) / $prevTotalDonations) * 100, 1)
            : 0;
        
        // Get average attendance for current period
        $avgAttendance = Attendance::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->avg('count') ?: 0;
        $avgAttendance = round($avgAttendance);
            
        // Get average attendance for previous period
        $prevAvgAttendance = Attendance::whereBetween('date', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])
            ->avg('count') ?: 0;
        $prevAvgAttendance = round($prevAvgAttendance);
        
        // Calculate attendance trend percentage
        $attendanceTrend = $prevAvgAttendance > 0 
            ? round((($avgAttendance - $prevAvgAttendance) / $prevAvgAttendance) * 100, 1)
            : 0;
        
        // Get new members for current period
        $newMembers = Member::whereBetween('created_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();
            
        // Get new members for previous period
        $prevNewMembers = Member::whereBetween('created_at', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])
            ->count();
        
        // Calculate member trend percentage
        $memberTrend = $prevNewMembers > 0 
            ? round((($newMembers - $prevNewMembers) / $prevNewMembers) * 100, 1)
            : 0;
        
        // Get pledge fulfillment percentage
        $totalPledgeAmount = Pledge::where('end_date', '>=', $startDate->toDateString())
            ->sum('amount');
            
        $totalPledgePaid = DB::table('donations')
            ->join('pledges', 'donations.pledge_id', '=', 'pledges.id')
            ->whereBetween('donations.donation_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('donations.amount');
            
        $pledgeFulfillment = $totalPledgeAmount > 0 
            ? round(($totalPledgePaid / $totalPledgeAmount) * 100, 1)
            : 0;
            
        // Get pledge fulfillment for previous period
        $prevTotalPledgePaid = DB::table('donations')
            ->join('pledges', 'donations.pledge_id', '=', 'pledges.id')
            ->whereBetween('donations.donation_date', [$prevStartDate->toDateString(), $prevEndDate->toDateString()])
            ->sum('donations.amount');
            
        $prevPledgeFulfillment = $totalPledgeAmount > 0 
            ? round(($prevTotalPledgePaid / $totalPledgeAmount) * 100, 1)
            : 0;
        
        // Calculate pledge trend percentage
        $pledgeTrend = $prevPledgeFulfillment > 0 
            ? round(($pledgeFulfillment - $prevPledgeFulfillment), 1)
            : 0;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'totalDonations' => $totalDonations,
                'donationTrend' => $donationTrend,
                'avgAttendance' => $avgAttendance,
                'attendanceTrend' => $attendanceTrend,
                'newMembers' => $newMembers,
                'memberTrend' => $memberTrend,
                'pledgeFulfillment' => $pledgeFulfillment,
                'pledgeTrend' => $pledgeTrend,
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'days' => $periodDays
                ]
            ]
        ]);
    }
    
    /**
     * Get donations chart data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function donationsChart(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date range',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Set default date range if not provided
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $endDate->copy()->subMonth();
        
        // Determine grouping based on date range
        $diffInDays = $startDate->diffInDays($endDate);
        $groupBy = 'day';
        $format = 'M d';
        
        if ($diffInDays > 90) {
            $groupBy = 'month';
            $format = 'M Y';
        } elseif ($diffInDays > 31) {
            $groupBy = 'week';
            $format = 'W';
        }
        
        // Get donation data grouped by the determined period
        $donationData = [];
        
        if ($groupBy === 'day') {
            $donations = Donation::select(
                    DB::raw('DATE(donation_date) as date'),
                    DB::raw('SUM(amount) as amount')
                )
                ->whereBetween('donation_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
                
            // Create a collection of all dates in the range
            $period = new \DatePeriod(
                $startDate,
                new \DateInterval('P1D'),
                $endDate->modify('+1 day')
            );
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $donation = $donations->firstWhere('date', $dateStr);
                
                $donationData[] = [
                    'date' => $dateStr,
                    'label' => $date->format($format),
                    'amount' => $donation ? $donation->amount : 0
                ];
            }
        } elseif ($groupBy === 'week') {
            $donations = Donation::select(
                    DB::raw('YEARWEEK(donation_date, 1) as yearweek'),
                    DB::raw('MIN(donation_date) as week_start'),
                    DB::raw('SUM(amount) as amount')
                )
                ->whereBetween('donation_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('yearweek')
                ->orderBy('yearweek')
                ->get();
                
            // Create weeks in the range
            $currentDate = $startDate->copy()->startOfWeek();
            while ($currentDate <= $endDate) {
                $weekStart = $currentDate->copy()->format('Y-m-d');
                $yearweek = $currentDate->copy()->format('oW');
                
                $donation = $donations->firstWhere('yearweek', $yearweek);
                
                $donationData[] = [
                    'date' => $weekStart,
                    'label' => 'Week ' . $currentDate->format($format),
                    'amount' => $donation ? $donation->amount : 0
                ];
                
                $currentDate->addWeek();
            }
        } else { // month
            $donations = Donation::select(
                    DB::raw('YEAR(donation_date) as year'),
                    DB::raw('MONTH(donation_date) as month'),
                    DB::raw('MIN(donation_date) as month_start'),
                    DB::raw('SUM(amount) as amount')
                )
                ->whereBetween('donation_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
                
            // Create months in the range
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->format('Y-m-d');
                $year = $currentDate->year;
                $month = $currentDate->month;
                
                $donation = $donations->first(function ($item) use ($year, $month) {
                    return $item->year == $year && $item->month == $month;
                });
                
                $donationData[] = [
                    'date' => $monthStart,
                    'label' => $currentDate->format($format),
                    'amount' => $donation ? $donation->amount : 0
                ];
                
                $currentDate->addMonth();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $donationData
        ]);
    }
    
    /**
     * Get attendance chart data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attendanceChart(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date range',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Set default date range if not provided
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : $endDate->copy()->subMonth();
        
        // Determine grouping based on date range
        $diffInDays = $startDate->diffInDays($endDate);
        $groupBy = 'day';
        $format = 'M d';
        
        if ($diffInDays > 90) {
            $groupBy = 'month';
            $format = 'M Y';
        } elseif ($diffInDays > 31) {
            $groupBy = 'week';
            $format = 'W';
        }
        
        // Get attendance data grouped by the determined period
        $attendanceData = [];
        
        if ($groupBy === 'day') {
            $attendances = Attendance::select(
                    DB::raw('DATE(date) as attend_date'),
                    DB::raw('SUM(count) as count')
                )
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('attend_date')
                ->orderBy('attend_date')
                ->get();
                
            // Create a collection of all dates in the range
            $period = new \DatePeriod(
                $startDate,
                new \DateInterval('P1D'),
                $endDate->modify('+1 day')
            );
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendances->firstWhere('attend_date', $dateStr);
                
                $attendanceData[] = [
                    'date' => $dateStr,
                    'label' => $date->format($format),
                    'count' => $attendance ? $attendance->count : 0
                ];
            }
        } elseif ($groupBy === 'week') {
            $attendances = Attendance::select(
                    DB::raw('YEARWEEK(date, 1) as yearweek'),
                    DB::raw('MIN(date) as week_start'),
                    DB::raw('SUM(count) as count')
                )
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('yearweek')
                ->orderBy('yearweek')
                ->get();
                
            // Create weeks in the range
            $currentDate = $startDate->copy()->startOfWeek();
            while ($currentDate <= $endDate) {
                $weekStart = $currentDate->copy()->format('Y-m-d');
                $yearweek = $currentDate->copy()->format('oW');
                
                $attendance = $attendances->firstWhere('yearweek', $yearweek);
                
                $attendanceData[] = [
                    'date' => $weekStart,
                    'label' => 'Week ' . $currentDate->format($format),
                    'count' => $attendance ? $attendance->count : 0
                ];
                
                $currentDate->addWeek();
            }
        } else { // month
            $attendances = Attendance::select(
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('MIN(date) as month_start'),
                    DB::raw('SUM(count) as count')
                )
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
                
            // Create months in the range
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate <= $endDate) {
                $monthStart = $currentDate->copy()->format('Y-m-d');
                $year = $currentDate->year;
                $month = $currentDate->month;
                
                $attendance = $attendances->first(function ($item) use ($year, $month) {
                    return $item->year == $year && $item->month == $month;
                });
                
                $attendanceData[] = [
                    'date' => $monthStart,
                    'label' => $currentDate->format($format),
                    'count' => $attendance ? $attendance->count : 0
                ];
                
                $currentDate->addMonth();
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $attendanceData
        ]);
    }
}
