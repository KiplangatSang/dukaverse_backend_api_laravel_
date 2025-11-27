<?php
namespace App\PaymentGateways\Stripe;

use App\PaymentGateways\IpayData;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripePayments extends IpayData
{
    private $stripe;
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret_key');
        $this->stripe = new StripeClient($this->secretKey);
    }

    /**
     * Create a Stripe payment intent
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @return array
     */
    public function createPaymentIntent($amount, $currency = 'usd', $description = 'Payment')
    {
        try {
            // Convert amount to cents for Stripe (if currency requires it)
            $amountInCents = $this->convertToStripeAmount($amount, $currency);

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => strtolower($currency),
                'description' => $description,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            Log::info('Stripe payment intent created successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => $currency
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'amount' => $amount,
                'currency' => $currency,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Confirm a Stripe payment intent
     *
     * @param string $paymentIntentId
     * @param string $paymentMethodId
     * @return array
     */
    public function confirmPaymentIntent($paymentIntentId, $paymentMethodId = null)
    {
        try {
            $confirmParams = [];

            if ($paymentMethodId) {
                $confirmParams['payment_method'] = $paymentMethodId;
            }

            $paymentIntent = $this->stripe->paymentIntents->confirm(
                $paymentIntentId,
                $confirmParams
            );

            Log::info('Stripe payment intent confirmed successfully', [
                'payment_intent_id' => $paymentIntentId,
                'status' => $paymentIntent->status
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100, // Convert back from cents
                'currency' => $paymentIntent->currency,
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment intent confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed'
            ];
        }
    }

    /**
     * Get payment intent status
     *
     * @param string $paymentIntentId
     * @return array
     */
    public function getPaymentIntent($paymentIntentId)
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'client_secret' => $paymentIntent->client_secret,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel a payment intent
     *
     * @param string $paymentIntentId
     * @return array
     */
    public function cancelPaymentIntent($paymentIntentId)
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->cancel($paymentIntentId);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a payment (legacy method for compatibility)
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @return array
     */
    public function processPayment($amount, $currency = 'USD', $description = 'Payment')
    {
        return $this->createPaymentIntent($amount, $currency, $description);
    }

    /**
     * Convert amount to Stripe format (cents for most currencies)
     *
     * @param float $amount
     * @param string $currency
     * @return int
     */
    private function convertToStripeAmount($amount, $currency)
    {
        // Stripe expects amounts in the smallest currency unit (e.g., cents for USD)
        // For currencies that don't have cents, the amount is in the base unit
        $zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return (int) $amount;
        }

        return (int) ($amount * 100);
    }

    /**
     * Handle Stripe webhook
     *
     * @param string $payload
     * @param string $signature
     * @return array
     */
    public function handleWebhook($payload, $signature)
    {
        try {
            $webhookSecret = config('services.stripe.webhook_secret');

            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

            Log::info('Stripe webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id
            ]);

            // Handle different event types
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Handle successful payment
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // Handle failed payment
                    break;
                // Add more event types as needed
            }

            return [
                'success' => true,
                'event_type' => $event->type,
                'event_id' => $event->id
            ];

        } catch (\Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
