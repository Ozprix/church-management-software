<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Refund;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Exception;
use Illuminate\Support\Facades\Log;

class StripePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    
    /**
     * Process a payment
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function processPayment(float $amount, array $paymentData): array
    {
        try {
            // Amount needs to be in cents for Stripe
            $amountInCents = (int)($amount * 100);
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd', // Can be made configurable
                'payment_method' => $paymentData['payment_method_id'] ?? null,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'description' => $paymentData['description'] ?? 'Church donation',
                'metadata' => [
                    'donation_id' => $paymentData['donation_id'] ?? null,
                    'member_id' => $paymentData['member_id'] ?? null,
                    'project_id' => $paymentData['project_id'] ?? null,
                    'category_id' => $paymentData['category_id'] ?? null,
                ],
                'receipt_email' => $paymentData['email'] ?? null,
            ]);
            
            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $amount,
                'currency' => 'usd',
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Process a refund
     *
     * @param string $paymentId
     * @param float|null $amount
     * @return array
     */
    public function processRefund(string $paymentId, ?float $amount = null): array
    {
        try {
            $refundData = [
                'payment_intent' => $paymentId,
            ];
            
            // If amount is specified, convert to cents and add to refund data
            if ($amount !== null) {
                $refundData['amount'] = (int)($amount * 100);
            }
            
            $refund = Refund::create($refundData);
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $amount ?? ($refund->amount / 100),
            ];
        } catch (Exception $e) {
            Log::error('Stripe refund processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Verify a payment webhook
     *
     * @param array $payload
     * @return bool
     */
    public function verifyWebhook(array $payload): bool
    {
        try {
            $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
            $webhookSecret = config('services.stripe.webhook_secret');
            
            $event = Webhook::constructEvent(
                file_get_contents('php://input'),
                $sigHeader,
                $webhookSecret
            );
            
            return true;
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Stripe webhook processing error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payment details
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentDetails(string $paymentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);
            
            return [
                'success' => true,
                'payment_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100, // Convert from cents
                'currency' => $paymentIntent->currency,
                'metadata' => $paymentIntent->metadata->toArray(),
                'created' => date('Y-m-d H:i:s', $paymentIntent->created),
            ];
        } catch (Exception $e) {
            Log::error('Stripe payment details error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate client token or keys needed for frontend
     *
     * @return array
     */
    public function generateClientToken(): array
    {
        return [
            'publishable_key' => config('services.stripe.key'),
        ];
    }
}
