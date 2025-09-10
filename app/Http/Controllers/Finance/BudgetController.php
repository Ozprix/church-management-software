<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Budget::query();
        
        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->where(function($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('start_date', '<=', $request->start_date)
                         ->where('end_date', '>=', $request->end_date);
                  });
            });
        }
        
        // Filter by active status
        if ($request->has('active') && $request->active === 'true') {
            $now = now()->format('Y-m-d');
            $query->where('start_date', '<=', $now)
                  ->where('end_date', '>=', $now)
                  ->where('status', 'active');
        }
        
        // Sort by start date by default, most recent first
        $sortField = $request->sort_by ?? 'start_date';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $budgets = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $budgets
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
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'required|string|max:100',
            'status' => 'required|in:active,inactive,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $budget = Budget::create([
            'name' => $request->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'spent_amount' => 0, // Initialize with zero
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'category' => $request->category,
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Budget created successfully',
            'data' => $budget
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
        $budget = Budget::with(['expenses' => function($query) {
            $query->orderBy('expense_date', 'desc');
        }])->findOrFail($id);

        // Add calculated attributes
        $budget->remaining = $budget->amount - $budget->spent_amount;
        $budget->utilization_percentage = $budget->amount > 0 ? ($budget->spent_amount / $budget->amount) * 100 : 0;
        $budget->is_active = now()->between($budget->start_date, $budget->end_date) && $budget->status === 'active';

        return response()->json([
            'status' => 'success',
            'data' => $budget
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
        $budget = Budget::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'category' => 'sometimes|required|string|max:100',
            'status' => 'sometimes|required|in:active,inactive,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $budget->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Budget updated successfully',
            'data' => $budget
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
        $budget = Budget::findOrFail($id);
        
        // Check if budget has any expenses
        $expenseCount = $budget->expenses()->count();
        
        if ($expenseCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete budget with associated expenses. Please reassign or delete the expenses first.'
            ], 400);
        }
        
        $budget->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Budget deleted successfully'
        ]);
    }
    
    /**
     * Get budget categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        $categories = Budget::select('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
            
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
    
    /**
     * Get budget overview.
     *
     * @return \Illuminate\Http\Response
     */
    public function overview()
    {
        $now = now()->format('Y-m-d');
        
        // Get active budgets
        $activeBudgets = Budget::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 'active')
            ->get();
            
        // Calculate total budget amount and spent amount
        $totalBudget = $activeBudgets->sum('amount');
        $totalSpent = $activeBudgets->sum('spent_amount');
        
        // Get budget utilization by category
        $byCategory = Budget::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 'active')
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(spent_amount) as total_spent'))
            ->groupBy('category')
            ->get()
            ->map(function($item) {
                $item->utilization_percentage = $item->total_amount > 0 ? ($item->total_spent / $item->total_amount) * 100 : 0;
                $item->remaining = $item->total_amount - $item->total_spent;
                return $item;
            });
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'remaining' => $totalBudget - $totalSpent,
                'utilization_percentage' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
                'active_budgets_count' => $activeBudgets->count(),
                'by_category' => $byCategory
            ]
        ]);
    }
}
