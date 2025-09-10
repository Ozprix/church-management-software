<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Pledge;
use App\Models\Member;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PledgeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Pledge::with(['member:id,first_name,last_name', 'campaign:id,name']);
        
        // Filter by member
        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        
        // Filter by campaign
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
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
        
        // Sort by pledge date by default, most recent first
        $sortField = $request->sort_by ?? 'pledge_date';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $pledges = $query->paginate($request->per_page ?? 15);
        
        return response()->json([
            'status' => 'success',
            'data' => $pledges
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
            'member_id' => 'required|exists:members,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'required|numeric|min:0.01',
            'pledge_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'frequency' => 'required|in:one-time,weekly,biweekly,monthly,quarterly,annually',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pledge = Pledge::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pledge created successfully',
            'data' => $pledge
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
        $pledge = Pledge::with([
            'member:id,first_name,last_name,email,phone',
            'campaign:id,name,description,target_amount',
            'donations' => function($query) {
                $query->orderBy('donation_date', 'desc');
            }
        ])->findOrFail($id);

        // Add calculated attributes
        $pledge->donated_amount = $pledge->donations->sum('amount');
        $pledge->remaining_amount = $pledge->amount - $pledge->donated_amount;
        $pledge->fulfillment_percentage = $pledge->amount > 0 ? ($pledge->donated_amount / $pledge->amount) * 100 : 0;
        $pledge->is_active = now()->between($pledge->start_date, $pledge->end_date) && $pledge->status === 'active';

        return response()->json([
            'status' => 'success',
            'data' => $pledge
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
        $pledge = Pledge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'member_id' => 'sometimes|required|exists:members,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'pledge_date' => 'sometimes|required|date',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'frequency' => 'sometimes|required|in:one-time,weekly,biweekly,monthly,quarterly,annually',
            'status' => 'sometimes|required|in:active,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $pledge->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pledge updated successfully',
            'data' => $pledge
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
        $pledge = Pledge::findOrFail($id);
        
        // Check if pledge has any donations
        $donationCount = $pledge->donations()->count();
        
        if ($donationCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete pledge with associated donations. Please reassign or delete the donations first.'
            ], 400);
        }
        
        $pledge->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pledge deleted successfully'
        ]);
    }
    
    /**
     * Get pledge statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        // Set default date range to current year if not provided
        $startDate = $request->start_date ?? date('Y-01-01');
        $endDate = $request->end_date ?? date('Y-12-31');
        
        // Total pledges
        $totalPledges = Pledge::whereBetween('pledge_date', [$startDate, $endDate])->count();
        
        // Total pledged amount
        $totalPledgedAmount = Pledge::whereBetween('pledge_date', [$startDate, $endDate])->sum('amount');
        
        // Active pledges
        $now = now()->format('Y-m-d');
        $activePledges = Pledge::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('status', 'active')
            ->count();
        
        // Completed pledges
        $completedPledges = Pledge::where('status', 'completed')
            ->whereBetween('pledge_date', [$startDate, $endDate])
            ->count();
        
        // Cancelled pledges
        $cancelledPledges = Pledge::where('status', 'cancelled')
            ->whereBetween('pledge_date', [$startDate, $endDate])
            ->count();
        
        // Pledges by frequency
        $pledgesByFrequency = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->select('frequency', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('frequency')
            ->get();
        
        // Pledges by campaign
        $pledgesByCampaign = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->leftJoin('campaigns', 'pledges.campaign_id', '=', 'campaigns.id')
            ->select(
                DB::raw('COALESCE(campaigns.name, "General Fund") as campaign_name'),
                DB::raw('COUNT(pledges.id) as count'),
                DB::raw('SUM(pledges.amount) as total_amount')
            )
            ->groupBy('campaign_name')
            ->orderBy('total_amount', 'desc')
            ->get();
        
        // Top pledgers
        $topPledgers = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->join('members', 'pledges.member_id', '=', 'members.id')
            ->select(
                'members.id',
                'members.first_name',
                'members.last_name',
                DB::raw('COUNT(pledges.id) as pledge_count'),
                DB::raw('SUM(pledges.amount) as total_amount')
            )
            ->groupBy('members.id', 'members.first_name', 'members.last_name')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_pledges' => $totalPledges,
                'total_pledged_amount' => $totalPledgedAmount,
                'active_pledges' => $activePledges,
                'completed_pledges' => $completedPledges,
                'cancelled_pledges' => $cancelledPledges,
                'pledges_by_frequency' => $pledgesByFrequency,
                'pledges_by_campaign' => $pledgesByCampaign,
                'top_pledgers' => $topPledgers,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }
    
    /**
     * Get upcoming pledge payments.
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming(Request $request)
    {
        $days = $request->days ?? 30;
        $now = now();
        $endDate = $now->copy()->addDays($days);
        
        $pledges = Pledge::with(['member:id,first_name,last_name', 'campaign:id,name'])
            ->where('status', 'active')
            ->where('end_date', '>=', $now->format('Y-m-d'))
            ->get()
            ->filter(function($pledge) use ($now, $endDate) {
                $nextPayment = $pledge->next_payment_date;
                return $nextPayment && $nextPayment->between($now, $endDate);
            })
            ->map(function($pledge) {
                return [
                    'id' => $pledge->id,
                    'member' => $pledge->member ? $pledge->member->first_name . ' ' . $pledge->member->last_name : 'Unknown',
                    'campaign' => $pledge->campaign ? $pledge->campaign->name : 'General Fund',
                    'amount' => $pledge->amount,
                    'donated_amount' => $pledge->donated_amount,
                    'remaining_amount' => $pledge->remaining_amount,
                    'next_payment_date' => $pledge->next_payment_date->format('Y-m-d'),
                    'days_until_payment' => $pledge->next_payment_date->diffInDays(now()),
                    'frequency' => $pledge->frequency
                ];
            })
            ->sortBy('next_payment_date')
            ->values();
        
        return response()->json([
            'status' => 'success',
            'data' => $pledges
        ]);
    }
}
