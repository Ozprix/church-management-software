<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\GroupAnalyticsServiceInterface;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\GroupMemberEngagementRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupAnalyticsController extends Controller
{
    /**
     * @var GroupAnalyticsServiceInterface
     */
    protected $groupAnalyticsService;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupMemberEngagementRepositoryInterface
     */
    protected $groupMemberEngagementRepository;

    /**
     * GroupAnalyticsController constructor.
     *
     * @param GroupAnalyticsServiceInterface $groupAnalyticsService
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupMemberEngagementRepositoryInterface $groupMemberEngagementRepository
     */
    public function __construct(
        GroupAnalyticsServiceInterface $groupAnalyticsService,
        GroupRepositoryInterface $groupRepository,
        GroupMemberEngagementRepositoryInterface $groupMemberEngagementRepository
    ) {
        $this->groupAnalyticsService = $groupAnalyticsService;
        $this->groupRepository = $groupRepository;
        $this->groupMemberEngagementRepository = $groupMemberEngagementRepository;
    }

    /**
     * Get analytics dashboard data for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboard(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $period = $request->input('period', '3months');
        $validPeriods = ['1month', '3months', '6months', '1year'];
        
        if (!in_array($period, $validPeriods)) {
            $period = '3months';
        }

        $dashboard = $this->groupAnalyticsService->getAnalyticsDashboard($groupId, $period);

        return response()->json($dashboard);
    }

    /**
     * Generate analytics for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $date = $request->input('date', now()->format('Y-m-d'));

        try {
            $analytics = $this->groupAnalyticsService->generateGroupAnalytics($groupId, $date);

            return response()->json([
                'status' => 'success',
                'message' => 'Analytics generated successfully',
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance analytics for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $analytics = $this->groupAnalyticsService->getAttendanceAnalytics($groupId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }

    /**
     * Get growth analytics for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrowthAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $analytics = $this->groupAnalyticsService->getGrowthAnalytics($groupId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }

    /**
     * Get engagement analytics for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEngagementAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $analytics = $this->groupAnalyticsService->getEngagementAnalytics($groupId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }

    /**
     * Get member engagement analytics for a group.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberEngagementAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $analytics = $this->groupAnalyticsService->getMemberEngagementAnalytics($groupId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }

    /**
     * Get engagement trend for a member in a group.
     *
     * @param Request $request
     * @param int $groupId
     * @param int $memberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberEngagementTrend(Request $request, $groupId, $memberId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $trend = $this->groupMemberEngagementRepository->getMemberEngagementTrend($groupId, $memberId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $trend
        ]);
    }

    /**
     * Get analytics comparison for multiple groups.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupsComparison(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_ids' => 'required|array',
            'group_ids.*' => 'required|integer|exists:groups,id',
            'metric' => 'required|string|in:attendance_rate,growth_rate,engagement_score,total_members,active_members,new_members',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $groupIds = $request->input('group_ids');
        $metric = $request->input('metric');
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));

        $comparison = $this->groupAnalyticsService->getGroupsComparison($groupIds, $metric, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $comparison
        ]);
    }

    /**
     * Get comprehensive analytics data for the frontend component.
     *
     * @param Request $request
     * @param int $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComprehensiveAnalytics(Request $request, $groupId)
    {
        // Check if group exists
        $group = $this->groupRepository->getGroupById($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Group not found'
            ], 404);
        }

        $timeRange = $request->input('time_range', 'month');
        $validTimeRanges = ['month', 'quarter', 'year'];
        
        if (!in_array($timeRange, $validTimeRanges)) {
            $timeRange = 'month';
        }

        try {
            // Use the new Group model method to get comprehensive analytics data
            $analyticsData = $group->getAnalyticsData($timeRange);
            
            return response()->json([
                'status' => 'success',
                'data' => $analyticsData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve analytics data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule analytics generation for all groups.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scheduleAnalyticsGeneration()
    {
        try {
            $count = $this->groupAnalyticsService->scheduleAnalyticsGeneration();

            return response()->json([
                'status' => 'success',
                'message' => "Analytics generated for {$count} groups"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
