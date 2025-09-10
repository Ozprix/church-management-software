<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\RecurringDonation;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RecurringDonationService
{
    protected $paymentService;
    protected $gatewayFactory;

    /**
     * Create a new service instance.
     *
     * @param PaymentService $paymentService
     * @param PaymentGatewayFactory $gatewayFactory
     */
    public function __construct(PaymentService $paymentService, PaymentGatewayFactory $gatewayFactory)
    {
        $this->paymentService = $paymentService;
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Process all due recurring donations.
     *
     * @return array
     */
    public function processDueRecurringDonations(): array
    {
        $dueRecurringDonations = RecurringDonation::dueForProcessing()->get();
        
        $results = [
            'total' => $dueRecurringDonations->count(),
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];
        
        foreach ($dueRecurringDonations as $recurringDonation) {
            try {
                $result = $this->processRecurringDonation($recurringDonation);
                
                if ($result['status'] === 'success') {
                    $results['success']++;
                } elseif ($result['status'] === 'skipped') {
                    $results['skipped']++;
                } else {
                    $results['failed']++;
                }
                
                $results['details'][] = $result;
            } catch (\Exception $e) {
                Log::error('Error processing recurring donation: ' . $e->getMessage(), [
                    'recurring_donation_id' => $recurringDonation->id,
                    'exception' => $e
                ]);
                
                $results['failed']++;
                $results['details'][] = [
                    'recurring_donation_id' => $recurringDonation->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Process a single recurring donation.
     *
     * @param RecurringDonation $recurringDonation
     * @return array
     */
    public function processRecurringDonation(RecurringDonation $recurringDonation): array
    {
        // Check if the recurring donation is still active and not expired
        if (!$recurringDonation->is_active) {
            return [
                'recurring_donation_id' => $recurringDonation->id,
                'status' => 'skipped',
                'message' => 'Recurring donation is inactive'
            ];
        }
        
        if ($recurringDonation->hasExpired()) {
            $recurringDonation->is_active = false;
            $recurringDonation->save();
            
            return [
                'recurring_donation_id' => $recurringDonation->id,
                'status' => 'skipped',
                'message' => 'Recurring donation has expired'
            ];
        }
        
        // Start a database transaction
        return DB::transaction(function () use ($recurringDonation) {
            // Create a new donation record
            $donation = new Donation([
                'member_id' => $recurringDonation->member_id,
                'category_id' => $recurringDonation->category_id,
                'project_id' => $recurringDonation->project_id,
                'campaign_id' => $recurringDonation->campaign_id,
                'amount' => $recurringDonation->amount,
                'payment_method' => $recurringDonation->payment_method,
                'donation_date' => Carbon::today(),
                'is_anonymous' => false,
                'is_recurring' => true,
                'recurring_frequency' => $recurringDonation->frequency,
                'payment_status' => 'pending',
                'receipt_number' => $this->generateReceiptNumber(),
                'notes' => 'Automatically generated from recurring donation #' . $recurringDonation->id,
                'recurring_donation_id' => $recurringDonation->id,
            ]);
            
            $donation->save();
            
            // Process the payment
            $paymentData = [
                'amount' => $recurringDonation->amount,
                'currency' => 'USD',
                'customer_id' => $recurringDonation->gateway_customer_id,
                'payment_method' => $recurringDonation->payment_method,
                'description' => 'Recurring donation - ' . $recurringDonation->frequency_text,
            ];
            
            $result = $this->paymentService->processDonationPayment(
                $donation, 
                $recurringDonation->payment_gateway, 
                $paymentData
            );
            
            if ($result['success']) {
                // Update the recurring donation with the new donation
                $recurringDonation->last_donation_id = $donation->id;
                $recurringDonation->updateNextDonationDate();
                
                return [
                    'recurring_donation_id' => $recurringDonation->id,
                    'donation_id' => $donation->id,
                    'status' => 'success',
                    'message' => 'Recurring donation processed successfully',
                    'transaction_id' => $result['transaction_id'] ?? null
                ];
            } else {
                // If payment failed, update the donation status
                $donation->payment_status = 'failed';
                $donation->save();
                
                // Log the failure but keep the recurring donation active
                // It will be retried on the next scheduled run
                return [
                    'recurring_donation_id' => $recurringDonation->id,
                    'donation_id' => $donation->id,
                    'status' => 'failed',
                    'message' => $result['message'] ?? 'Payment processing failed',
                    'error' => $result['error'] ?? null
                ];
            }
        });
    }

    /**
     * Create a new recurring donation.
     *
     * @param array $data
     * @return RecurringDonation
     */
    public function createRecurringDonation(array $data): RecurringDonation
    {
        // Set up the next donation date
        $startDate = Carbon::parse($data['start_date']);
        $nextDonationDate = $startDate;
        
        // If the start date is today, set the next donation date based on frequency
        if ($startDate->isToday()) {
            $tempRecurring = new RecurringDonation([
                'frequency' => $data['frequency'],
                'start_date' => $startDate,
            ]);
            
            $nextDonationDate = $tempRecurring->calculateNextDonationDate($startDate);
        }
        
        // Create the recurring donation record
        $recurringDonation = new RecurringDonation([
            'member_id' => $data['member_id'],
            'category_id' => $data['category_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_gateway' => $data['payment_gateway'],
            'frequency' => $data['frequency'],
            'start_date' => $startDate,
            'end_date' => isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            'next_donation_date' => $nextDonationDate,
            'is_active' => true,
            'gateway_subscription_id' => $data['gateway_subscription_id'] ?? null,
            'gateway_customer_id' => $data['gateway_customer_id'] ?? null,
            'gateway_data' => $data['gateway_data'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        
        $recurringDonation->save();
        
        // If the start date is today, create the initial donation
        if ($startDate->isToday() && isset($data['process_initial_donation']) && $data['process_initial_donation']) {
            $this->createInitialDonation($recurringDonation, $data);
        }
        
        return $recurringDonation;
    }

    /**
     * Create the initial donation for a recurring donation.
     *
     * @param RecurringDonation $recurringDonation
     * @param array $data
     * @return Donation|null
     */
    protected function createInitialDonation(RecurringDonation $recurringDonation, array $data): ?Donation
    {
        // Create a new donation record
        $donation = new Donation([
            'member_id' => $recurringDonation->member_id,
            'category_id' => $recurringDonation->category_id,
            'project_id' => $recurringDonation->project_id,
            'campaign_id' => $recurringDonation->campaign_id,
            'amount' => $recurringDonation->amount,
            'payment_method' => $recurringDonation->payment_method,
            'donation_date' => Carbon::today(),
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_recurring' => true,
            'recurring_frequency' => $recurringDonation->frequency,
            'payment_status' => 'pending',
            'receipt_number' => $this->generateReceiptNumber(),
            'notes' => 'Initial donation for recurring donation #' . $recurringDonation->id,
            'recurring_donation_id' => $recurringDonation->id,
        ]);
        
        $donation->save();
        
        // Process the payment
        $paymentData = [
            'amount' => $recurringDonation->amount,
            'currency' => 'USD',
            'customer_id' => $recurringDonation->gateway_customer_id,
            'payment_method' => $data['payment_method_id'] ?? null,
            'description' => 'Initial recurring donation - ' . $recurringDonation->frequency_text,
        ];
        
        $result = $this->paymentService->processDonationPayment(
            $donation, 
            $recurringDonation->payment_gateway, 
            $paymentData
        );
        
        if ($result['success']) {
            // Update the recurring donation with the new donation
            $recurringDonation->last_donation_id = $donation->id;
            $recurringDonation->save();
            
            return $donation;
        } else {
            // If payment failed, update the donation status
            $donation->payment_status = 'failed';
            $donation->save();
            
            Log::error('Failed to process initial donation for recurring donation', [
                'recurring_donation_id' => $recurringDonation->id,
                'donation_id' => $donation->id,
                'error' => $result['error'] ?? null
            ]);
            
            return $donation;
        }
    }

    /**
     * Cancel a recurring donation.
     *
     * @param RecurringDonation $recurringDonation
     * @param string|null $reason
     * @return bool
     */
    public function cancelRecurringDonation(RecurringDonation $recurringDonation, ?string $reason = null): bool
    {
        // If there's a subscription ID, cancel it with the payment gateway
        if ($recurringDonation->gateway_subscription_id) {
            try {
                $gateway = $this->gatewayFactory->create($recurringDonation->payment_gateway);
                $gateway->cancelSubscription($recurringDonation->gateway_subscription_id);
            } catch (\Exception $e) {
                Log::error('Failed to cancel subscription with payment gateway', [
                    'recurring_donation_id' => $recurringDonation->id,
                    'gateway' => $recurringDonation->payment_gateway,
                    'subscription_id' => $recurringDonation->gateway_subscription_id,
                    'error' => $e->getMessage()
                ]);
                
                // Continue with cancellation even if gateway call fails
            }
        }
        
        // Update the recurring donation
        $recurringDonation->is_active = false;
        $recurringDonation->notes = $recurringDonation->notes . "\n\nCancelled on " . Carbon::now()->format('Y-m-d H:i:s');
        
        if ($reason) {
            $recurringDonation->notes .= "\nReason: " . $reason;
        }
        
        return $recurringDonation->save();
    }

    /**
     * Update a recurring donation.
     *
     * @param RecurringDonation $recurringDonation
     * @param array $data
     * @return RecurringDonation
     */
    public function updateRecurringDonation(RecurringDonation $recurringDonation, array $data): RecurringDonation
    {
        $amountChanged = isset($data['amount']) && $data['amount'] != $recurringDonation->amount;
        $frequencyChanged = isset($data['frequency']) && $data['frequency'] != $recurringDonation->frequency;
        $paymentMethodChanged = isset($data['payment_method']) && $data['payment_method'] != $recurringDonation->payment_method;
        
        // Update the recurring donation fields
        if (isset($data['category_id'])) {
            $recurringDonation->category_id = $data['category_id'];
        }
        
        if (isset($data['project_id'])) {
            $recurringDonation->project_id = $data['project_id'];
        }
        
        if (isset($data['campaign_id'])) {
            $recurringDonation->campaign_id = $data['campaign_id'];
        }
        
        if (isset($data['amount'])) {
            $recurringDonation->amount = $data['amount'];
        }
        
        if (isset($data['frequency'])) {
            $recurringDonation->frequency = $data['frequency'];
            
            // Recalculate next donation date if frequency changed
            if ($frequencyChanged) {
                $recurringDonation->next_donation_date = $recurringDonation->calculateNextDonationDate(Carbon::today());
            }
        }
        
        if (isset($data['payment_method'])) {
            $recurringDonation->payment_method = $data['payment_method'];
        }
        
        if (isset($data['end_date'])) {
            $recurringDonation->end_date = $data['end_date'] ? Carbon::parse($data['end_date']) : null;
        }
        
        if (isset($data['notes'])) {
            $recurringDonation->notes = $data['notes'];
        }
        
        // If payment method, amount, or frequency changed and there's a subscription ID,
        // update the subscription with the payment gateway
        if (($amountChanged || $frequencyChanged || $paymentMethodChanged) && $recurringDonation->gateway_subscription_id) {
            try {
                $gateway = $this->gatewayFactory->create($recurringDonation->payment_gateway);
                
                $subscriptionData = [
                    'subscription_id' => $recurringDonation->gateway_subscription_id,
                    'amount' => $recurringDonation->amount,
                    'currency' => 'USD',
                ];
                
                if ($paymentMethodChanged && isset($data['payment_method_id'])) {
                    $subscriptionData['payment_method_id'] = $data['payment_method_id'];
                }
                
                $gateway->updateSubscription($subscriptionData);
            } catch (\Exception $e) {
                Log::error('Failed to update subscription with payment gateway', [
                    'recurring_donation_id' => $recurringDonation->id,
                    'gateway' => $recurringDonation->payment_gateway,
                    'subscription_id' => $recurringDonation->gateway_subscription_id,
                    'error' => $e->getMessage()
                ]);
                
                // Continue with update even if gateway call fails
            }
        }
        
        $recurringDonation->save();
        
        return $recurringDonation;
    }

    /**
     * Generate a unique receipt number.
     *
     * @return string
     */
    protected function generateReceiptNumber(): string
    {
        $prefix = 'REC-';
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        return $prefix . $date . '-' . $random;
    }
}
