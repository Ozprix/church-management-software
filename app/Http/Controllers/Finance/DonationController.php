<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Campaign;
use App\Models\DonationCategory;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationReceiptMail;
use App\Services\TaxReceiptService;

class DonationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Donation::with(['member', 'recipient', 'category', 'project', 'campaign']);
        
        // Filter by member if provided
        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        
        // Filter by recipient if provided
        if ($request->has('recipient_id')) {
            $query->where('recipient_id', $request->recipient_id);
        }
        
        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by project if provided
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by campaign if provided
        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Filter by payment method if provided
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('donation_date', [$request->start_date, $request->end_date]);
        }
        
        // Filter by minimum amount if provided
        if ($request->has('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        
        // Filter by maximum amount if provided
        if ($request->has('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }
        
        // Filter by recurring status if provided
        if ($request->has('is_recurring')) {
            $query->where('is_recurring', $request->is_recurring === 'true');
        }
        
        // Filter by anonymous status if provided
        if ($request->has('is_anonymous')) {
            $query->where('is_anonymous', $request->is_anonymous === 'true');
        }
        
        // Filter by gift status if provided
        if ($request->has('is_gift')) {
            if ($request->is_gift === 'true') {
                $query->whereNotNull('recipient_id');
            } else {
                $query->whereNull('recipient_id');
            }
        }
        
        // Sort by donation date by default, most recent first
        $sortField = $request->sort_by ?? 'donation_date';
        $sortDirection = $request->sort_dir ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        $donations = $query->paginate($request->per_page ?? 15);
        
        // Calculate total amount for the filtered donations
        $totalAmount = $query->sum('amount');
        
        return response()->json([
            'status' => 'success',
            'data' => $donations,
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
            'member_id' => 'required|exists:members,id',
            'recipient_id' => 'nullable|exists:members,id|different:member_id',
            'category_id' => 'nullable|exists:donation_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
            'donation_date' => 'required|date',
            'is_anonymous' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|string|in:weekly,monthly,quarterly,yearly',
            'recurring_start_date' => 'nullable|required_if:is_recurring,true|date',
            'recurring_end_date' => 'nullable|date|after_or_equal:recurring_start_date',
            'notes' => 'nullable|string',
            'gift_message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a receipt number if not provided
        if (!$request->has('receipt_number')) {
            $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $request->merge(['receipt_number' => $receiptNumber]);
        }
        
        $donation = Donation::create($request->all());

        // If this donation is associated with a campaign, update the campaign's raised amount
        if ($request->has('campaign_id') && $request->campaign_id) {
            $campaign = Campaign::find($request->campaign_id);
            if ($campaign) {
                $campaign->update([
                    'raised_amount' => $campaign->raised_amount + $request->amount
                ]);
            }
        }
        
        // If this donation is associated with a project, update the project's current amount
        if ($request->has('project_id') && $request->project_id) {
            $project = Project::find($request->project_id);
            if ($project) {
                $project->update([
                    'current_amount' => $project->current_amount + $request->amount
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Donation recorded successfully',
            'data' => $donation
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
        $donation = Donation::with(['member', 'recipient', 'category', 'project', 'campaign'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $donation
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
        $donation = Donation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'member_id' => 'sometimes|required|exists:members,id',
            'recipient_id' => 'nullable|exists:members,id|different:member_id',
            'category_id' => 'nullable|exists:donation_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|string|max:50',
            'transaction_id' => 'nullable|string|max:255',
            'donation_date' => 'sometimes|required|date',
            'is_anonymous' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|string|in:weekly,monthly,quarterly,yearly',
            'recurring_start_date' => 'nullable|required_if:is_recurring,true|date',
            'recurring_end_date' => 'nullable|date|after_or_equal:recurring_start_date',
            'notes' => 'nullable|string',
            'gift_message' => 'nullable|string|max:500',
            'receipt_sent' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If the amount is being changed and this donation is associated with a campaign,
        // update the campaign's raised amount
        if ($request->has('amount') && $request->amount != $donation->amount && $donation->campaign_id) {
            $campaign = Campaign::find($donation->campaign_id);
            if ($campaign) {
                $campaign->update([
                    'raised_amount' => $campaign->raised_amount - $donation->amount + $request->amount
                ]);
            }
        }
        
        // If the campaign is being changed, update both the old and new campaign's raised amounts
        if ($request->has('campaign_id') && $request->campaign_id != $donation->campaign_id) {
            // Subtract amount from old campaign if it exists
            if ($donation->campaign_id) {
                $oldCampaign = Campaign::find($donation->campaign_id);
                if ($oldCampaign) {
                    $oldCampaign->update([
                        'raised_amount' => $oldCampaign->raised_amount - $donation->amount
                    ]);
                }
            }
            
            // Add amount to new campaign if it exists
            if ($request->campaign_id) {
                $newCampaign = Campaign::find($request->campaign_id);
                if ($newCampaign) {
                    $newCampaign->update([
                        'raised_amount' => $newCampaign->raised_amount + ($request->has('amount') ? $request->amount : $donation->amount)
                    ]);
                }
            }
        }
        
        // If the project is being changed, update both the old and new project's current amounts
        if ($request->has('project_id') && $request->project_id != $donation->project_id) {
            // Subtract amount from old project if it exists
            if ($donation->project_id) {
                $oldProject = Project::find($donation->project_id);
                if ($oldProject) {
                    $oldProject->update([
                        'current_amount' => $oldProject->current_amount - $donation->amount
                    ]);
                }
            }
            
            // Add amount to new project if it exists
            if ($request->project_id) {
                $newProject = Project::find($request->project_id);
                if ($newProject) {
                    $newProject->update([
                        'current_amount' => $newProject->current_amount + ($request->has('amount') ? $request->amount : $donation->amount)
                    ]);
                }
            }
        }
        
        // If just the amount is changing and the project stays the same, update the project amount
        if ($request->has('amount') && $request->amount != $donation->amount && $donation->project_id && (!$request->has('project_id') || $request->project_id == $donation->project_id)) {
            $project = Project::find($donation->project_id);
            if ($project) {
                $project->update([
                    'current_amount' => $project->current_amount - $donation->amount + $request->amount
                ]);
            }
        }

        $donation->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Donation updated successfully',
            'data' => $donation
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
        $donation = Donation::findOrFail($id);
        
        // If this donation is associated with a campaign, update the campaign's raised amount
        if ($donation->campaign_id) {
            $campaign = Campaign::find($donation->campaign_id);
            if ($campaign) {
                $campaign->update([
                    'raised_amount' => $campaign->raised_amount - $donation->amount
                ]);
            }
        }
        
        // If this donation is associated with a project, update the project's current amount
        if ($donation->project_id) {
            $project = Project::find($donation->project_id);
            if ($project) {
                $project->update([
                    'current_amount' => $project->current_amount - $donation->amount
                ]);
            }
        }
        
        $donation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Donation deleted successfully'
        ]);
    }
    
    /**
     * Get donation statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        // Set default date range to current year if not provided
        $startDate = $request->start_date ?? date('Y-01-01');
        $endDate = $request->end_date ?? date('Y-12-31');
        
        // Total donations amount
        $totalAmount = Donation::whereBetween('donation_date', [$startDate, $endDate])->sum('amount');
        
        // Total number of donations
        $totalCount = Donation::whereBetween('donation_date', [$startDate, $endDate])->count();
        
        // Average donation amount
        $averageAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;
        
        // Donations by payment method
        $byPaymentMethod = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();
        
        // Donations by month (for the selected period)
        $byMonth = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select(DB::raw('YEAR(donation_date) as year'), DB::raw('MONTH(donation_date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Top donors
        $topDonors = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select('member_id', DB::raw('SUM(amount) as total'))
            ->with('member:id,first_name,last_name,email')
            ->groupBy('member_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Recurring donations
        $recurringTotal = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->where('is_recurring', true)
            ->sum('amount');
        
        $recurringCount = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->where('is_recurring', true)
            ->count();
            
        // Donations by category
        $byCategory = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->with('category:id,name')
            ->groupBy('category_id')
            ->get();
            
        // Donations by project
        $byProject = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->whereNotNull('project_id')
            ->select('project_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->with('project:id,name,goal_amount')
            ->groupBy('project_id')
            ->get();
            
        // Gift donations
        $giftTotal = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->whereNotNull('recipient_id')
            ->sum('amount');
            
        $giftCount = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->whereNotNull('recipient_id')
            ->count();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_amount' => $totalAmount,
                'total_count' => $totalCount,
                'average_amount' => $averageAmount,
                'by_payment_method' => $byPaymentMethod,
                'by_month' => $byMonth,
                'top_donors' => $topDonors,
                'recurring' => [
                    'total' => $recurringTotal,
                    'count' => $recurringCount
                ],
                'by_category' => $byCategory,
                'by_project' => $byProject,
                'gifts' => [
                    'total' => $giftTotal,
                    'count' => $giftCount
                ],
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }
    
    /**
     * Send receipt for a donation.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function sendReceipt(string $id, TaxReceiptService $taxReceiptService)
    {
        $donation = Donation::with(['member', 'category', 'project', 'campaign', 'recipient'])->findOrFail($id);

        if (!$donation->member || empty($donation->member->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member does not have an email address'
            ], 422);
        }

        // Generate or fetch existing tax receipt PDF using TaxReceiptService
        $receipt = $taxReceiptService->generateReceiptForDonation($donation);

        // Send to donor
        Mail::to($donation->member->email)->queue(new DonationReceiptMail($donation, attachPdf: (bool) $receipt, attachmentPath: $receipt?->file_path));

        // Optionally send to gift recipient if donation is a gift and recipient has email
        if (request()->boolean('send_to_gift_recipient') && $donation->recipient && $donation->recipient->email) {
            Mail::to($donation->recipient->email)->queue(new DonationReceiptMail($donation, attachPdf: (bool) $receipt, attachmentPath: $receipt?->file_path));
        }

        $donation->update([
            'receipt_sent' => true,
            'receipt_sent_at' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Receipt sent successfully',
            'data' => $donation
        ]);
    }
    
    /**
     * Get donations for a specific member.
     *
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function memberDonations($memberId)
    {
        $member = Member::findOrFail($memberId);
        
        $donations = Donation::with(['category', 'project', 'campaign'])
            ->where('member_id', $memberId)
            ->orderBy('donation_date', 'desc')
            ->paginate(15);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'member' => $member,
                'donations' => $donations,
                'total_donated' => Donation::where('member_id', $memberId)->sum('amount')
            ]
        ]);
    }
    
    /**
     * Get gifts received by a specific member.
     *
     * @param  int  $memberId
     * @return \Illuminate\Http\Response
     */
    public function memberGiftsReceived($memberId)
    {
        $member = Member::findOrFail($memberId);
        
        $gifts = Donation::with(['member:id,first_name,last_name,profile_photo'])
            ->where('recipient_id', $memberId)
            ->orderBy('donation_date', 'desc')
            ->paginate(15);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'member' => $member,
                'gifts' => $gifts,
                'total_received' => Donation::where('recipient_id', $memberId)->sum('amount')
            ]
        ]);
    }
}
