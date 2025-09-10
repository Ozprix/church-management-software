<?php

namespace App\Services\Payment;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;
use Exception;
use Illuminate\Support\Facades\Log;

class PayPalPaymentGateway implements PaymentGatewayInterface
{
    /**
     * PayPal API Context
     *
     * @var ApiContext
     */
    private $apiContext;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Set up PayPal API Context
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
        );
        
        // Set PayPal mode (sandbox or live)
        $this->apiContext->setConfig([
            'mode' => config('services.paypal.mode', 'sandbox'),
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'DEBUG',
        ]);
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
            // Create a new payer
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            
            // Set amount
            $paypalAmount = new Amount();
            $paypalAmount->setCurrency('USD')
                ->setTotal($amount);
            
            // Set transaction
            $transaction = new Transaction();
            $transaction->setAmount($paypalAmount)
                ->setDescription($paymentData['description'] ?? 'Church donation')
                ->setInvoiceNumber(uniqid('DONATION-'));
            
            // Set redirect URLs
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(route('donations.paypal.success'))
                ->setCancelUrl(route('donations.paypal.cancel'));
            
            // Create payment
            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);
            
            // Create payment with PayPal
            $payment->create($this->apiContext);
            
            // Get redirect URL
            $approvalUrl = $payment->getApprovalLink();
            
            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'approval_url' => $approvalUrl,
                'amount' => $amount,
                'currency' => 'USD',
            ];
        } catch (Exception $e) {
            Log::error('PayPal payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Execute PayPal payment after approval
     *
     * @param string $paymentId
     * @param string $payerId
     * @return array
     */
    public function executePayment(string $paymentId, string $payerId): array
    {
        try {
            // Get payment details
            $payment = Payment::get($paymentId, $this->apiContext);
            
            // Execute payment
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);
            
            // Execute payment
            $result = $payment->execute($execution, $this->apiContext);
            
            return [
                'success' => true,
                'payment_id' => $result->getId(),
                'status' => $result->getState(),
                'amount' => $result->getTransactions()[0]->getAmount()->getTotal(),
                'currency' => $result->getTransactions()[0]->getAmount()->getCurrency(),
            ];
        } catch (Exception $e) {
            Log::error('PayPal payment execution error: ' . $e->getMessage());
            
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
            // Get sale ID from payment
            $payment = Payment::get($paymentId, $this->apiContext);
            $transactions = $payment->getTransactions();
            $relatedResources = $transactions[0]->getRelatedResources();
            $sale = $relatedResources[0]->getSale();
            $saleId = $sale->getId();
            
            // Create refund
            $refundRequest = new RefundRequest();
            
            // If amount is specified, set it
            if ($amount !== null) {
                $refundAmount = new Amount();
                $refundAmount->setCurrency('USD')
                    ->setTotal($amount);
                $refundRequest->setAmount($refundAmount);
            }
            
            // Process refund
            $sale = new Sale();
            $sale->setId($saleId);
            $refund = $sale->refund($refundRequest, $this->apiContext);
            
            return [
                'success' => true,
                'refund_id' => $refund->getId(),
                'status' => $refund->getState(),
                'amount' => $amount ?? $refund->getAmount()->getTotal(),
            ];
        } catch (Exception $e) {
            Log::error('PayPal refund processing error: ' . $e->getMessage());
            
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
            // PayPal webhook verification logic
            // This is simplified and should be expanded based on PayPal's webhook verification guidelines
            $headers = getallheaders();
            $webhookId = config('services.paypal.webhook_id');
            
            // In a real implementation, you would verify the webhook signature
            // For now, just check if the event type is valid
            if (isset($payload['event_type']) && in_array($payload['event_type'], [
                'PAYMENT.SALE.COMPLETED',
                'PAYMENT.SALE.REFUNDED',
            ])) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('PayPal webhook verification error: ' . $e->getMessage());
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
            $payment = Payment::get($paymentId, $this->apiContext);
            
            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'status' => $payment->getState(),
                'amount' => $payment->getTransactions()[0]->getAmount()->getTotal(),
                'currency' => $payment->getTransactions()[0]->getAmount()->getCurrency(),
                'created' => $payment->getCreateTime(),
            ];
        } catch (Exception $e) {
            Log::error('PayPal payment details error: ' . $e->getMessage());
            
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
            'client_id' => config('services.paypal.client_id'),
            'mode' => config('services.paypal.mode', 'sandbox'),
        ];
    }
}
