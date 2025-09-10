<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Member;
use App\Models\TaxReceipt;
use App\Services\TaxReceiptService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaxReceiptController extends Controller
{
    protected $taxReceiptService;

    /**
     * Create a new controller instance.
     *
     * @param TaxReceiptService $taxReceiptService
     */
    public function __construct(TaxReceiptService $taxReceiptService)
    {
        $this->taxReceiptService = $taxReceiptService;
    }

    /**
     * Display a listing of tax receipts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TaxReceipt::with(['member', 'donation']);
            
            // Apply filters
            if ($request->has('member_id')) {
                $query->where('member_id', $request->input('member_id'));
            }
            
            if ($request->has('tax_year')) {
                $query->where('tax_year', $request->input('tax_year'));
            }
            
            if ($request->has('is_annual')) {
                $query->where('is_annual', $request->boolean('is_annual'));
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Sort
            $sortField = $request->input('sort_field', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);
            
            // Paginate
            $perPage = $request->input('per_page', 15);
            $taxReceipts = $query->paginate($perPage);
            
            return response()->json($taxReceipts);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tax receipts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a tax receipt for a donation.
     *
     * @param Request $request
     * @param int $donationId
     * @return JsonResponse
     */
    public function generateForDonation(Request $request, int $donationId): JsonResponse
    {
        try {
            $donation = Donation::findOrFail($donationId);
            
            $receipt = $this->taxReceiptService->generateReceiptForDonation($donation);
            
            if ($receipt) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tax receipt generated successfully',
                    'data' => $receipt->load('member')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate tax receipt. Donation may not be eligible.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate tax receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate an annual tax receipt for a member.
     *
     * @param Request $request
     * @param int $memberId
     * @return JsonResponse
     */
    public function generateAnnualForMember(Request $request, int $memberId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_year' => 'required|integer|min:2000|max:' . (date('Y')),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $member = Member::findOrFail($memberId);
            $year = $request->input('tax_year');
            
            $receipt = $this->taxReceiptService->generateAnnualReceiptForMember($member, $year);
            
            if ($receipt) {
                return response()->json([
                    'success' => true,
                    'message' => 'Annual tax receipt generated successfully',
                    'data' => $receipt->load('member')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate annual tax receipt. Member may not have eligible donations for the year.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate annual tax receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate annual tax receipts for all members.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateAllAnnualReceipts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_year' => 'required|integer|min:2000|max:' . (date('Y')),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $year = $request->input('tax_year');
            
            $results = $this->taxReceiptService->generateAllAnnualReceipts($year);
            
            return response()->json([
                'success' => true,
                'message' => 'Annual tax receipts generation process completed',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate annual tax receipts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a tax receipt by email.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function sendByEmail(Request $request, int $id): JsonResponse
    {
        try {
            $receipt = TaxReceipt::findOrFail($id);
            
            $result = $this->taxReceiptService->sendReceiptByEmail($receipt);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tax receipt sent successfully',
                    'data' => $receipt->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send tax receipt'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send tax receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void a tax receipt.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function voidReceipt(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $receipt = TaxReceipt::findOrFail($id);
            
            $result = $this->taxReceiptService->voidReceipt($receipt, $request->input('reason'));
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tax receipt voided successfully',
                    'data' => $receipt->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to void tax receipt'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to void tax receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a tax receipt PDF.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function download(int $id)
    {
        try {
            $receipt = TaxReceipt::findOrFail($id);
            
            if (!$receipt->file_path || !Storage::disk('public')->exists($receipt->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receipt file not found'
                ], 404);
            }
            
            $filePath = Storage::disk('public')->path($receipt->file_path);
            $fileName = $receipt->is_annual 
                ? 'Annual_Tax_Receipt_' . $receipt->receipt_number . '.pdf'
                : 'Tax_Receipt_' . $receipt->receipt_number . '.pdf';
            
            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download tax receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax receipts for a specific member.
     *
     * @param int $memberId
     * @return JsonResponse
     */
    public function getMemberReceipts(int $memberId): JsonResponse
    {
        try {
            $member = Member::findOrFail($memberId);
            
            $receipts = TaxReceipt::where('member_id', $memberId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $receipts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tax receipts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a summary of tax receipts by year.
     *
     * @return JsonResponse
     */
    public function getYearlySummary(): JsonResponse
    {
        try {
            $summary = TaxReceipt::selectRaw('tax_year, COUNT(*) as receipt_count, SUM(amount) as total_amount, SUM(CASE WHEN is_annual = 1 THEN 1 ELSE 0 END) as annual_count')
                ->groupBy('tax_year')
                ->orderBy('tax_year', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tax receipt summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get tax receipt statistics
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => TaxReceipt::count(),
                'totalAmount' => TaxReceipt::sum('amount'),
                'annualCount' => TaxReceipt::where('is_annual', true)->count(),
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tax receipt statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
