<?php
namespace App\PaymentGateways\PayPal;

use App\PaymentGateways\IpayData;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

class PayPalPayments extends IpayData
{
    private PayPalHttpClient $client;

    public function __construct()
    {
        $this->initializeClient();
    }

    private function initializeClient()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $mode = config('services.paypal.mode', 'sandbox');

        if ($mode === 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }

        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder($amount, $currency = 'USD', $description = 'Payment')
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $currency,
                        "value" => number_format($amount, 2, '.', ''),
                    ],
                    "description" => $description,
                ]
            ],
            "application_context" => [
                "cancel_url" => url('/paypal/cancel'),
                "return_url" => url('/paypal/success'),
            ]
        ];

        try {
            $response = $this->client->execute($request);
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('PayPal order creation failed: ' . $e->getMessage());
        }
    }

    public function captureOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);

        try {
            $response = $this->client->execute($request);
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('PayPal order capture failed: ' . $e->getMessage());
        }
    }

    public function getOrder($orderId)
    {
        $request = new OrdersGetRequest($orderId);

        try {
            $response = $this->client->execute($request);
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('PayPal order retrieval failed: ' . $e->getMessage());
        }
    }

    public function processPayment($amount, $currency = 'USD', $description = 'Payment')
    {
        // Create order
        $orderResponse = $this->createOrder($amount, $currency, $description);
        $orderId = $orderResponse->result->id;

        // For immediate capture, you can capture right away
        // In a typical flow, you'd redirect to PayPal for approval first
        // $captureResponse = $this->captureOrder($orderId);

        return [
            'order_id' => $orderId,
            'status' => 'created',
            'approval_url' => collect($orderResponse->result->links)->firstWhere('rel', 'approve')->href ?? null,
        ];
    }
}
