<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Process a payment
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function processPayment(float $amount, array $paymentData): array;
    
    /**
     * Process a refund
     *
     * @param string $paymentId
     * @param float|null $amount
     * @return array
     */
    public function processRefund(string $paymentId, ?float $amount = null): array;
    
    /**
     * Verify a payment webhook
     *
     * @param array $payload
     * @return bool
     */
    public function verifyWebhook(array $payload): bool;
    
    /**
     * Get payment details
     *
     * @param string $paymentId
     * @return array
     */
    public function getPaymentDetails(string $paymentId): array;
    
    /**
     * Generate client token or keys needed for frontend
     *
     * @return array
     */
    public function generateClientToken(): array;
}
