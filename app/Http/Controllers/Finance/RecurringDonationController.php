<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\RecurringDonation;
use App\Models\Donation;
use App\Models\Member;
use App\Services\RecurringDonationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RecurringDonationController extends Controller
{
    protected $recurringDonationService;

    /**
     * Create a new controller instance.
     *
     * @param RecurringDonationService $recurringDonationService
     */
    public function __construct(RecurringDonationService $recurringDonationService)
    {
        $this->recurringDonationService = $recurringDonationService;
    }

    /**
     * Display a listing of recurring donations.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RecurringDonation::with(['member', 'category', 'project', 'campaign', 'lastDonation']);
            
            // Apply filters
            if ($request->has('member_id')) {
                $query->where('member_id', $request->input('member_id'));
            }
            
            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }
            
            if ($request->has('project_id')) {
                $query->where('project_id', $request->input('project_id'));
            }
            
            if ($request->has('campaign_id')) {
                $query->where('campaign_id', $request->input('campaign_id'));
            }
            
            if ($request->has('frequency')) {
                $query->where('frequency', $request->input('frequency'));
            }
            
            if ($request->has('status')) {
                if ($request->input('status') === 'active') {
                    $query->active();
                } elseif ($request->input('status') === 'inactive') {
                    $query->inactive();
                }
            }
            
            // Sort
            $sortField = $request->input('sort_field', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // Paginate
            $perPage = $request->input('per_page', 15);
            $recurringDonations = $query->paginate($perPage);
            
            return response()->json($recurringDonations);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recurring donations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created recurring donation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'category_id' => 'nullable|exists:donation_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_gateway' => 'required|string',
            'frequency' => 'required|in:weekly,biweekly,monthly,quarterly,biannually,annually',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'gateway_subscription_id' => 'nullable|string',
            'gateway_customer_id' => 'required|string',
            'gateway_data' => 'nullable|array',
            'notes' => 'nullable|string',
            'process_initial_donation' => 'nullable|boolean',
            'payment_method_id' => 'nullable|string',
            'is_anonymous' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $recurringDonation = $this->recurringDonationService->createRecurringDonation($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Recurring donation created successfully',
                'data' => $recurringDonation->load(['member', 'category', 'project', 'campaign'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create recurring donation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified recurring donation.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $recurringDonation = RecurringDonation::with([
                'member', 
                'category', 
                'project', 
                'campaign', 
                'lastDonation',
                'donations' => function ($query) {
                    $query->orderBy('donation_date', 'desc');
                }
            ])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $recurringDonation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recurring donation',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified recurring donation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:donation_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'nullable|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'frequency' => 'nullable|in:weekly,biweekly,monthly,quarterly,biannually,annually',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $recurringDonation = RecurringDonation::findOrFail($id);
            
            $updatedRecurringDonation = $this->recurringDonationService->updateRecurringDonation(
                $recurringDonation,
                $request->all()
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Recurring donation updated successfully',
                'data' => $updatedRecurringDonation->load(['member', 'category', 'project', 'campaign'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recurring donation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the specified recurring donation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $recurringDonation = RecurringDonation::findOrFail($id);
            
            $result = $this->recurringDonationService->cancelRecurringDonation(
                $recurringDonation,
                $request->input('reason')
            );
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recurring donation cancelled successfully',
                    'data' => $recurringDonation->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel recurring donation'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel recurring donation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process due recurring donations manually.
     *
     * @return JsonResponse
     */
    public function processDueRecurringDonations(): JsonResponse
    {
        try {
            $results = $this->recurringDonationService->processDueRecurringDonations();
            
            return response()->json([
                'success' => true,
                'message' => 'Processed recurring donations',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process recurring donations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donations for a specific recurring donation.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getDonations(int $id): JsonResponse
    {
        try {
            $recurringDonation = RecurringDonation::findOrFail($id);
            
            $donations = Donation::where('recurring_donation_id', $id)
                ->orderBy('donation_date', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $donations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve donations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recurring donations for a specific member.
     *
     * @param int $memberId
     * @return JsonResponse
     */
    public function getMemberRecurringDonations(int $memberId): JsonResponse
    {
        try {
            $member = Member::findOrFail($memberId);
            
            $recurringDonations = RecurringDonation::with(['category', 'project', 'campaign', 'lastDonation'])
                ->where('member_id', $memberId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $recurringDonations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recurring donations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
