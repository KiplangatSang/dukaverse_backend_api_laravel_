<?php
namespace App\Services;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use App\Repositories\TransactionsRepository;
use Illuminate\Http\Request;

class PaymentService extends BaseService
{
    //

    public function __construct(
        StoreFileResource $storeFileResource,
        ResponseHelper $responseHelper,
        private readonly TransactionsRepository $transactionRepository
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function ecommercePaymentGatewaysAvailable()
    {

    }

    public function setEcommercePaymentGateways()
    {

    }

    public function removeEcommercePaymentGateways()
    {

    }

    public function clearEcommercePaymentGateways()
    {}

    public function processGooglePay(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:127',
        ]);

        try {
            $googlePay = new \App\PaymentGateways\GooglePay\GooglePayPayments();
            $result = $googlePay->processPayment(
                $request->amount,
                $request->currency ?? 'USD',
                $request->description ?? 'Payment',
                $request->token
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Google Pay payment processed successfully',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Pay payment failed',
                    'error' => $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google Pay payment failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function processPayPalPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:127',
        ]);

        try {
            $paypal = new \App\PaymentGateways\PayPal\PayPalPayments();
            $result = $paypal->processPayment(
                $request->amount,
                $request->currency ?? 'USD',
                $request->description ?? 'Payment'
            );

            return response()->json([
                'success' => true,
                'message' => 'PayPal order created successfully',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal payment failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function capturePayPalPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        try {
            $paypal = new \App\PaymentGateways\PayPal\PayPalPayments();
            $result = $paypal->captureOrder($request->order_id);

            return response()->json([
                'success' => true,
                'message' => 'PayPal payment captured successfully',
                'data' => $result->result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal capture failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function createStripePaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $stripe = new \App\PaymentGateways\Stripe\StripePayments();
            $result = $stripe->createPaymentIntent(
                $request->amount,
                $request->currency ?? 'USD',
                $request->description ?? 'Payment'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stripe payment intent created successfully',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe payment intent creation failed',
                    'error' => $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe payment intent creation failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function confirmStripePayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'nullable|string',
        ]);

        try {
            $stripe = new \App\PaymentGateways\Stripe\StripePayments();
            $result = $stripe->confirmPaymentIntent(
                $request->payment_intent_id,
                $request->payment_method_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stripe payment confirmed successfully',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe payment confirmation failed',
                    'error' => $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe payment confirmation failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function handleStripeWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('Stripe-Signature');

            $stripe = new \App\PaymentGateways\Stripe\StripePayments();
            $result = $stripe->handleWebhook($payload, $signature);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stripe webhook processed successfully',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe webhook processing failed',
                    'error' => $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe webhook processing failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

     public function storeTransaction(
        $gateway,
$sender_account_id,
$receiver_account_id,
$amount,
$message,
$transaction_type,
$cost,
$currency,
$purpose,
$sender_phone_number,
$receiver_phone_number,
$purpose_id,
     )
    {
        # code...
        $transactiondata = $this->transactionRepository->saveTransaction(
            $gateway,
            $sender_account_id,
            $receiver_account_id,
            $amount,
            $message,
            $transaction_type,
            $cost,
            $currency,
            $purpose,
            $sender_phone_number,
            $receiver_phone_number,
            $purpose_id,
        );

        return $transactiondata;
    }

}
