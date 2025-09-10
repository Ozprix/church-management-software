<?php

namespace App\Services\Payment;

use App\Models\Donation;
use App\Models\PaymentTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process a donation payment
     *
     * @param Donation $donation
     * @param string $gateway
     * @param array $paymentData
     * @return array
     */
    public function processDonationPayment(Donation $donation, string $gateway, array $paymentData): array
    {
        try {
            // Get payment gateway
            $paymentGateway = PaymentGatewayFactory::create($gateway);
            
            // Add donation data to payment data
            $paymentData['donation_id'] = $donation->id;
            $paymentData['member_id'] = $donation->member_id;
            $paymentData['project_id'] = $donation->project_id;
            $paymentData['category_id'] = $donation->category_id;
            $paymentData['description'] = $this->generateDonationDescription($donation);
            
            // Process payment
            $result = $paymentGateway->processPayment($donation->amount, $paymentData);
            
            // If payment was successful, update donation and create transaction record
            if ($result['success']) {
                DB::transaction(function () use ($donation, $result, $gateway) {
                    // Update donation with payment information
                    $donation->update([
                        'transaction_id' => $result['payment_id'],
                        'payment_method' => $gateway,
                        'payment_status' => $result['status'],
                    ]);
                    
                    // Create payment transaction record
                    $this->createPaymentTransaction($donation, $result, $gateway);
                });
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Process a donation refund
     *
     * @param Donation $donation
     * @param float|null $amount
     * @return array
     */
    public function processDonationRefund(Donation $donation, ?float $amount = null): array
    {
        try {
            // Check if donation has a transaction ID
            if (!$donation->transaction_id) {
                return [
                    'success' => false,
                    'error' => 'No transaction ID found for this donation',
                ];
            }
            
            // Get payment gateway
            $paymentGateway = PaymentGatewayFactory::create($donation->payment_method);
            
            // Process refund
            $result = $paymentGateway->processRefund($donation->transaction_id, $amount);
            
            // If refund was successful, update donation
            if ($result['success']) {
                DB::transaction(function () use ($donation, $result, $amount) {
                    // Update donation with refund information
                    $donation->update([
                        'refund_id' => $result['refund_id'],
                        'refund_amount' => $amount ?? $donation->amount,
                        'payment_status' => 'refunded',
                    ]);
                    
                    // Create refund transaction record
                    $this->createRefundTransaction($donation, $result);
                });
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Refund processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Handle payment webhook
     *
     * @param string $gateway
     * @param array $payload
     * @return bool
     */
    public function handleWebhook(string $gateway, array $payload): bool
    {
        try {
            // Get payment gateway
            $paymentGateway = PaymentGatewayFactory::create($gateway);
            
            // Verify webhook
            if (!$paymentGateway->verifyWebhook($payload)) {
                return false;
            }
            
            // Process webhook based on gateway
            switch ($gateway) {
                case PaymentGatewayFactory::GATEWAY_STRIPE:
                    return $this->processStripeWebhook($payload);
                case PaymentGatewayFactory::GATEWAY_PAYPAL:
                    return $this->processPayPalWebhook($payload);
                default:
                    return false;
            }
        } catch (Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process Stripe webhook
     *
     * @param array $payload
     * @return bool
     */
    private function processStripeWebhook(array $payload): bool
    {
        $eventType = $payload['type'] ?? '';
        $data = $payload['data']['object'] ?? [];
        
        switch ($eventType) {
            case 'payment_intent.succeeded':
                // Find donation by transaction ID
                $donation = Donation::where('transaction_id', $data['id'])->first();
                
                if ($donation) {
                    $donation->update([
                        'payment_status' => 'completed',
                    ]);
                    
                    // Update project amount if donation is for a project
                    $this->updateProjectAmount($donation);
                }
                
                return true;
                
            case 'payment_intent.payment_failed':
                // Find donation by transaction ID
                $donation = Donation::where('transaction_id', $data['id'])->first();
                
                if ($donation) {
                    $donation->update([
                        'payment_status' => 'failed',
                    ]);
                }
                
                return true;
                
            default:
                return false;
        }
    }
    
    /**
     * Process PayPal webhook
     *
     * @param array $payload
     * @return bool
     */
    private function processPayPalWebhook(array $payload): bool
    {
        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];
        
        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                // Find donation by transaction ID
                $paymentId = $resource['parent_payment'] ?? '';
                $donation = Donation::where('transaction_id', $paymentId)->first();
                
                if ($donation) {
                    $donation->update([
                        'payment_status' => 'completed',
                    ]);
                    
                    // Update project amount if donation is for a project
                    $this->updateProjectAmount($donation);
                }
                
                return true;
                
            case 'PAYMENT.SALE.REFUNDED':
                // Find donation by transaction ID
                $paymentId = $resource['parent_payment'] ?? '';
                $donation = Donation::where('transaction_id', $paymentId)->first();
                
                if ($donation) {
                    $donation->update([
                        'payment_status' => 'refunded',
                        'refund_amount' => $resource['amount']['total'] ?? $donation->amount,
                    ]);
                }
                
                return true;
                
            default:
                return false;
        }
    }
    
    /**
     * Create payment transaction record
     *
     * @param Donation $donation
     * @param array $paymentResult
     * @param string $gateway
     * @return PaymentTransaction
     */
    private function createPaymentTransaction(Donation $donation, array $paymentResult, string $gateway): PaymentTransaction
    {
        return PaymentTransaction::create([
            'donation_id' => $donation->id,
            'transaction_id' => $paymentResult['payment_id'],
            'gateway' => $gateway,
            'amount' => $donation->amount,
            'currency' => $paymentResult['currency'] ?? 'USD',
            'status' => $paymentResult['status'],
            'type' => 'payment',
            'metadata' => json_encode($paymentResult),
        ]);
    }
    
    /**
     * Create refund transaction record
     *
     * @param Donation $donation
     * @param array $refundResult
     * @return PaymentTransaction
     */
    private function createRefundTransaction(Donation $donation, array $refundResult): PaymentTransaction
    {
        return PaymentTransaction::create([
            'donation_id' => $donation->id,
            'transaction_id' => $refundResult['refund_id'],
            'gateway' => $donation->payment_method,
            'amount' => $refundResult['amount'],
            'currency' => 'USD',
            'status' => $refundResult['status'],
            'type' => 'refund',
            'metadata' => json_encode($refundResult),
        ]);
    }
    
    /**
     * Update project amount if donation is for a project
     *
     * @param Donation $donation
     * @return void
     */
    private function updateProjectAmount(Donation $donation): void
    {
        if ($donation->project_id && $donation->payment_status === 'completed') {
            $project = $donation->project;
            
            if ($project) {
                $project->increment('current_amount', $donation->amount);
            }
        }
    }
    
    /**
     * Generate donation description
     *
     * @param Donation $donation
     * @return string
     */
    private function generateDonationDescription(Donation $donation): string
    {
        $description = 'Donation';
        
        if ($donation->category && $donation->category->name) {
            $description .= ' - ' . $donation->category->name;
        }
        
        if ($donation->project && $donation->project->name) {
            $description .= ' for ' . $donation->project->name;
        }
        
        return $description;
    }
}
