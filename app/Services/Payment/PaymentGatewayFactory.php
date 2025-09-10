<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    /**
     * Available payment gateways
     */
    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_PAYPAL = 'paypal';
    
    /**
     * Create a payment gateway instance
     *
     * @param string $gateway
     * @return PaymentGatewayInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $gateway): PaymentGatewayInterface
    {
        switch ($gateway) {
            case self::GATEWAY_STRIPE:
                return new StripePaymentGateway();
            case self::GATEWAY_PAYPAL:
                return new PayPalPaymentGateway();
            default:
                throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}");
        }
    }
    
    /**
     * Get list of available payment gateways
     *
     * @return array
     */
    public static function getAvailableGateways(): array
    {
        return [
            self::GATEWAY_STRIPE => 'Stripe',
            self::GATEWAY_PAYPAL => 'PayPal',
        ];
    }
}
