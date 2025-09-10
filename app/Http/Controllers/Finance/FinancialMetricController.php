<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\FinancialMetricRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FinancialMetricController extends Controller
{
    protected $metricRepository;

    /**
     * Create a new controller instance.
     *
     * @param FinancialMetricRepositoryInterface $metricRepository
     */
    public function __construct(FinancialMetricRepositoryInterface $metricRepository)
    {
        $this->middleware('auth:api');
        $this->metricRepository = $metricRepository;
    }

    /**
     * Get metrics for a specific date range.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricsByDateRange(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metrics = $this->metricRepository->getMetricsBetweenDates(
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($metrics);
    }

    /**
     * Get metrics with a specific name.
     *
     * @param string $metricName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricsByName($metricName)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $metrics = $this->metricRepository->getMetricsByName($metricName);

        return response()->json($metrics);
    }

    /**
     * Get metrics with a specific category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricsByCategory($category)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $metrics = $this->metricRepository->getMetricsByCategory($category);

        return response()->json($metrics);
    }

    /**
     * Get metrics with a specific name for a date range.
     *
     * @param Request $request
     * @param string $metricName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricsByNameAndDateRange(Request $request, $metricName)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metrics = $this->metricRepository->getMetricsByNameAndDateRange(
            $metricName,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($metrics);
    }

    /**
     * Get metrics with a specific name and category for a date range.
     *
     * @param Request $request
     * @param string $metricName
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricsByNameCategoryAndDateRange(Request $request, $metricName, $category)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metrics = $this->metricRepository->getMetricsByNameCategoryAndDateRange(
            $metricName,
            $category,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($metrics);
    }

    /**
     * Create a new financial metric.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('create_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'metric_name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'value' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metric = $this->metricRepository->createMetric($request->all());

        return response()->json($metric, 201);
    }

    /**
     * Update a financial metric.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('update_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'metric_name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:255',
            'value' => 'sometimes|required|numeric',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->metricRepository->updateMetric($id, $request->all());

        if (!$result) {
            return response()->json(['error' => 'Metric not found'], 404);
        }

        return response()->json(['message' => 'Metric updated successfully']);
    }

    /**
     * Delete a financial metric.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('delete_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->metricRepository->deleteMetric($id);

        if (!$result) {
            return response()->json(['error' => 'Metric not found'], 404);
        }

        return response()->json(['message' => 'Metric deleted successfully']);
    }

    /**
     * Get trend data for a specific metric over time.
     *
     * @param Request $request
     * @param string $metricName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetricTrend(Request $request, $metricName)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'interval' => 'sometimes|required|string|in:day,week,month,quarter,year',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interval = $request->input('interval', 'month');

        $trend = $this->metricRepository->getMetricTrend(
            $metricName,
            $request->input('start_date'),
            $request->input('end_date'),
            $interval
        );

        return response()->json($trend);
    }

    /**
     * Calculate and store key financial metrics for a specific date.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateAndStoreMetrics(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('create_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->metricRepository->calculateAndStoreMetrics($request->input('date'));

        if (!$result) {
            return response()->json(['error' => 'Failed to calculate metrics'], 500);
        }

        return response()->json(['message' => 'Metrics calculated and stored successfully']);
    }

    /**
     * Get the latest value for a specific metric.
     *
     * @param string $metricName
     * @param string|null $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestMetricValue($metricName, $category = null)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $value = $this->metricRepository->getLatestMetricValue($metricName, $category);

        if ($value === null) {
            return response()->json(['error' => 'Metric not found'], 404);
        }

        return response()->json(['value' => $value]);
    }

    /**
     * Get a summary of key financial metrics for a specific period.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancialSummary(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $summary = $this->metricRepository->getFinancialSummary(
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json($summary);
    }

    /**
     * Generate financial dashboard data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_financial_metrics')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Set default date range to current year if not provided
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // Get financial summary
        $summary = $this->metricRepository->getFinancialSummary($startDate, $endDate);

        // Get donation trend
        $donationTrend = $this->metricRepository->getMetricTrend('monthly_donations', $startDate, $endDate, 'month');

        // Get expense trend
        $expenseTrend = $this->metricRepository->getMetricTrend('monthly_expenses', $startDate, $endDate, 'month');

        // Get donor count trend
        $donorTrend = $this->metricRepository->getMetricTrend('donor_count', $startDate, $endDate, 'month');

        // Combine data for dashboard
        $dashboardData = [
            'summary' => $summary,
            'trends' => [
                'donations' => $donationTrend,
                'expenses' => $expenseTrend,
                'donors' => $donorTrend,
            ],
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];

        return response()->json($dashboardData);
    }
}
