<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BudgetAllocationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BudgetAllocationController extends Controller
{
    protected $budgetAllocationRepository;

    /**
     * Create a new controller instance.
     *
     * @param BudgetAllocationRepositoryInterface $budgetAllocationRepository
     */
    public function __construct(BudgetAllocationRepositoryInterface $budgetAllocationRepository)
    {
        $this->middleware('auth:api');
        $this->budgetAllocationRepository = $budgetAllocationRepository;
    }

    /**
     * Get all budget allocations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $filters = $request->only([
            'budget_id', 'department', 'ministry', 'project', 'category', 
            'over_budget', 'near_limit', 'threshold', 'search'
        ]);
        $perPage = $request->input('per_page', 15);

        $allocations = $this->budgetAllocationRepository->getAllAllocations($filters, $perPage);

        return response()->json($allocations);
    }

    /**
     * Get a specific budget allocation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocation = $this->budgetAllocationRepository->getAllocationById($id);

        if (!$allocation) {
            return response()->json(['error' => 'Budget allocation not found'], 404);
        }

        return response()->json($allocation);
    }

    /**
     * Create a new budget allocation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('create_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'budget_id' => 'required|exists:budgets,id',
            'department' => 'nullable|string|max:255',
            'ministry' => 'nullable|string|max:255',
            'project' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'allocated_amount' => 'required|numeric|min:0',
            'used_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $allocation = $this->budgetAllocationRepository->createAllocation($request->all());

        return response()->json($allocation, 201);
    }

    /**
     * Update a budget allocation.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check permission
        if (!Auth::user()->can('update_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocation = $this->budgetAllocationRepository->getAllocationById($id);

        if (!$allocation) {
            return response()->json(['error' => 'Budget allocation not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'budget_id' => 'sometimes|required|exists:budgets,id',
            'department' => 'nullable|string|max:255',
            'ministry' => 'nullable|string|max:255',
            'project' => 'nullable|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'allocated_amount' => 'sometimes|required|numeric|min:0',
            'used_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->budgetAllocationRepository->updateAllocation($id, $request->all());

        $updatedAllocation = $this->budgetAllocationRepository->getAllocationById($id);

        return response()->json($updatedAllocation);
    }

    /**
     * Delete a budget allocation.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Check permission
        if (!Auth::user()->can('delete_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocation = $this->budgetAllocationRepository->getAllocationById($id);

        if (!$allocation) {
            return response()->json(['error' => 'Budget allocation not found'], 404);
        }

        $this->budgetAllocationRepository->deleteAllocation($id);

        return response()->json(['message' => 'Budget allocation deleted successfully']);
    }

    /**
     * Get allocations for a specific budget.
     *
     * @param int $budgetId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocationsForBudget($budgetId)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getAllocationsForBudget($budgetId);

        return response()->json($allocations);
    }

    /**
     * Get allocations for a specific department.
     *
     * @param string $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocationsForDepartment($department)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getAllocationsForDepartment($department);

        return response()->json($allocations);
    }

    /**
     * Get allocations for a specific ministry.
     *
     * @param string $ministry
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocationsForMinistry($ministry)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getAllocationsForMinistry($ministry);

        return response()->json($allocations);
    }

    /**
     * Get allocations for a specific project.
     *
     * @param string $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocationsForProject($project)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getAllocationsForProject($project);

        return response()->json($allocations);
    }

    /**
     * Get allocations for a specific category.
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocationsForCategory($category)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getAllocationsForCategory($category);

        return response()->json($allocations);
    }

    /**
     * Get allocations that are over budget.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOverBudgetAllocations()
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocations = $this->budgetAllocationRepository->getOverBudgetAllocations();

        return response()->json($allocations);
    }

    /**
     * Get allocations that are near their limit.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearLimitAllocations(Request $request)
    {
        // Check permission
        if (!Auth::user()->can('view_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $threshold = $request->input('threshold', 90);
        $allocations = $this->budgetAllocationRepository->getNearLimitAllocations($threshold);

        return response()->json($allocations);
    }

    /**
     * Update the used amount for a specific allocation based on related expenses.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUsedAmount($id)
    {
        // Check permission
        if (!Auth::user()->can('update_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allocation = $this->budgetAllocationRepository->getAllocationById($id);

        if (!$allocation) {
            return response()->json(['error' => 'Budget allocation not found'], 404);
        }

        $result = $this->budgetAllocationRepository->updateUsedAmount($id);

        if (!$result) {
            return response()->json(['error' => 'Failed to update used amount'], 500);
        }

        $updatedAllocation = $this->budgetAllocationRepository->getAllocationById($id);

        return response()->json($updatedAllocation);
    }

    /**
     * Update used amounts for all allocations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAllUsedAmounts()
    {
        // Check permission
        if (!Auth::user()->can('update_budget_allocations')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $updatedCount = $this->budgetAllocationRepository->updateAllUsedAmounts();

        return response()->json([
            'message' => 'Updated used amounts for ' . $updatedCount . ' allocations',
            'updated_count' => $updatedCount
        ]);
    }
}
