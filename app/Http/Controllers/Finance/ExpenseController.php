<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Expense::query();
        
        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by payment method if provided
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }
        
        // Filter by minimum amount if provided
        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        
        // Filter by maximum amount if provided
        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }
        
        // Filter by budget_id if provided
        if ($request->has('budget_id')) {
            $query->where('budget_id', $request->budget_id);
        }
        
        // Sort by expense date by default, most recent first
        $sortField = $request->sort_by ?? 'expense_date';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $expenses = $query->paginate($request->per_page ?? 15);
        
        // Calculate total amount for the filtered expenses
        $totalAmount = $query->sum('amount');
        
        return response()->json([
            'status' => 'success',
            'data' => $expenses,
            'total_amount' => $totalAmount
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
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:100',
            'payment_method' => 'required|string|max:50',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'budget_id' => 'nullable|exists:budgets,id',
            'approved_by' => 'nullable|string|max:255',
            'recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expenseData = $request->except('receipt');
        
        // Handle receipt upload if provided
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $expenseData['receipt_path'] = $path;
        }

        $expense = Expense::create($expenseData);

        // If this expense is associated with a budget, update the budget's spent amount
        if ($request->has('budget_id') && $request->budget_id) {
            $budget = Budget::find($request->budget_id);
            if ($budget) {
                $budget->update([
                    'spent_amount' => $budget->spent_amount + $request->amount
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Expense recorded successfully',
            'data' => $expense
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
        $expense = Expense::with('budget')->findOrFail($id);
        
        // Add receipt URL if receipt exists
        if ($expense->receipt_path) {
            $expense->receipt_url = Storage::url($expense->receipt_path);
        }

        return response()->json([
            'status' => 'success',
            'data' => $expense
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
        $expense = Expense::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category' => 'sometimes|required|string|max:100',
            'payment_method' => 'sometimes|required|string|max:50',
            'expense_date' => 'sometimes|required|date',
            'vendor' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'budget_id' => 'nullable|exists:budgets,id',
            'approved_by' => 'nullable|string|max:255',
            'recurring' => 'boolean',
            'recurring_frequency' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expenseData = $request->except('receipt');
        
        // Handle receipt upload if provided
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            
            $path = $request->file('receipt')->store('receipts', 'public');
            $expenseData['receipt_path'] = $path;
        }

        // If the amount is being changed and this expense is associated with a budget,
        // update the budget's spent amount
        if ($request->has('amount') && $request->amount != $expense->amount && $expense->budget_id) {
            $budget = Budget::find($expense->budget_id);
            if ($budget) {
                $budget->update([
                    'spent_amount' => $budget->spent_amount - $expense->amount + $request->amount
                ]);
            }
        }
        
        // If the budget is being changed, update both the old and new budget's spent amounts
        if ($request->has('budget_id') && $request->budget_id != $expense->budget_id) {
            // Subtract amount from old budget if it exists
            if ($expense->budget_id) {
                $oldBudget = Budget::find($expense->budget_id);
                if ($oldBudget) {
                    $oldBudget->update([
                        'spent_amount' => $oldBudget->spent_amount - $expense->amount
                    ]);
                }
            }
            
            // Add amount to new budget if it exists
            if ($request->budget_id) {
                $newBudget = Budget::find($request->budget_id);
                if ($newBudget) {
                    $newBudget->update([
                        'spent_amount' => $newBudget->spent_amount + ($request->has('amount') ? $request->amount : $expense->amount)
                    ]);
                }
            }
        }

        $expense->update($expenseData);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense updated successfully',
            'data' => $expense
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
        $expense = Expense::findOrFail($id);
        
        // If this expense is associated with a budget, update the budget's spent amount
        if ($expense->budget_id) {
            $budget = Budget::find($expense->budget_id);
            if ($budget) {
                $budget->update([
                    'spent_amount' => $budget->spent_amount - $expense->amount
                ]);
            }
        }
        
        // Delete receipt file if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        
        $expense->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense deleted successfully'
        ]);
    }
    
    /**
     * Get expense statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        // Set default date range to current year if not provided
        $startDate = $request->start_date ?? date('Y-01-01');
        $endDate = $request->end_date ?? date('Y-12-31');
        
        // Total expenses amount
        $totalAmount = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        
        // Total number of expenses
        $totalCount = Expense::whereBetween('expense_date', [$startDate, $endDate])->count();
        
        // Average expense amount
        $averageAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;
        
        // Expenses by category
        $byCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
        
        // Expenses by payment method
        $byPaymentMethod = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // Expenses by month (for the selected period)
        $byMonth = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select(DB::raw('YEAR(expense_date) as year'), DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Top vendors
        $topVendors = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereNotNull('vendor')
            ->select('vendor', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('vendor')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Budget utilization
        $budgetUtilization = Budget::with(['expenses' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            }])
            ->whereRaw('? BETWEEN start_date AND end_date OR ? BETWEEN start_date AND end_date', [$startDate, $endDate])
            ->select('id', 'name', 'amount', 'spent_amount')
            ->get()
            ->map(function($budget) {
                $budget->utilization_percentage = $budget->amount > 0 ? ($budget->spent_amount / $budget->amount) * 100 : 0;
                return $budget;
            });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_amount' => $totalAmount,
                'total_count' => $totalCount,
                'average_amount' => $averageAmount,
                'by_category' => $byCategory,
                'by_payment_method' => $byPaymentMethod,
                'by_month' => $byMonth,
                'top_vendors' => $topVendors,
                'budget_utilization' => $budgetUtilization,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }
}
