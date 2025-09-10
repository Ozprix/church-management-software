<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * Constructor
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process a payment for a donation
     *
     * @param Request $request
     * @param int $donationId
     * @return JsonResponse
     */
    public function processPayment(Request $request, int $donationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gateway' => 'required|string|in:stripe,paypal',
            'payment_method_id' => 'required_if:gateway,stripe',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get donation
            $donation = Donation::findOrFail($donationId);
            
            // Process payment
            $result = $this->paymentService->processDonationPayment(
                $donation,
                $request->input('gateway'),
                $request->all()
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process a refund for a donation
     *
     * @param Request $request
     * @param int $donationId
     * @return JsonResponse
     */
    public function processRefund(Request $request, int $donationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get donation
            $donation = Donation::findOrFail($donationId);
            
            // Check if donation can be refunded
            if (!$donation->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only completed donations can be refunded',
                ], 400);
            }
            
            // Process refund
            $result = $this->paymentService->processDonationRefund(
                $donation,
                $request->input('amount')
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Refund processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to process refund: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment webhook
     *
     * @param Request $request
     * @param string $gateway
     * @return JsonResponse
     */
    public function handleWebhook(Request $request, string $gateway): JsonResponse
    {
        try {
            // Validate gateway
            if (!in_array($gateway, array_keys(PaymentGatewayFactory::getAvailableGateways()))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid payment gateway',
                ], 400);
            }
            
            // Process webhook
            $result = $this->paymentService->handleWebhook($gateway, $request->all());
            
            if ($result) {
                return response()->json(['success' => true]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to process webhook',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to process webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get client token for payment gateway
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'gateway' => 'required|string|in:stripe,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get payment gateway
            $paymentGateway = PaymentGatewayFactory::create($request->input('gateway'));
            
            // Generate client token
            $token = $paymentGateway->generateClientToken();
            
            return response()->json([
                'success' => true,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            Log::error('Client token generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate client token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available payment gateways
     *
     * @return JsonResponse
     */
    public function getAvailableGateways(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'gateways' => PaymentGatewayFactory::getAvailableGateways(),
        ]);
    }

    /**
     * Get payment transaction details
     *
     * @param int $donationId
     * @return JsonResponse
     */
    public function getTransactions(int $donationId): JsonResponse
    {
        try {
            // Get donation
            $donation = Donation::with('transactions')->findOrFail($donationId);
            
            return response()->json([
                'success' => true,
                'donation' => $donation,
                'transactions' => $donation->transactions,
            ]);
        } catch (\Exception $e) {
            Log::error('Transaction details error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get transaction details: ' . $e->getMessage(),
            ], 500);
        }
    }
}
