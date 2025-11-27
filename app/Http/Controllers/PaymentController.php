<?php
namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="Manage eCommerce payment gateways and process payments"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/payments/gateways",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="List available eCommerce payment gateways",
     *     description="Retrieve a list of available payment gateways for eCommerce transactions.",
     *     operationId="getEcommercePaymentGateways",
     *     @OA\Response(
     *         response=200,
     *         description="List of available gateways",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 example="mpesa, stripe, paypal, google_pay"
     *             )
     *         )
     *     )
     * )
     */
    public function ecommercePaymentGatewaysAvailable()
    {
        // Example available gateways - You may fetch from DB or config
        $gateways = ['mpesa', 'stripe', 'paypal', 'google_pay'];

        return response()->json(['gateways' => $gateways], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/gateways",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Set active eCommerce payment gateways",
     *     description="Configure and enable selected payment gateways for use in your store.",
     *     operationId="setEcommercePaymentGateways",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gateways"},
     *             @OA\Property(property="gateways", type="array", @OA\Items(type="string", example="paypal"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gateways successfully configured",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Payment gateways updated successfully")
     *         )
     *     )
     * )
     */
    public function setEcommercePaymentGateways(Request $request)
    {
        $request->validate([
            'gateways' => 'required|array|min:1',
        ]);

        // Save to database or config store
        // PaymentGateway::updateActiveGateways($request->gateways);

        return response()->json(['message' => 'Payment gateways updated successfully'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/payments/gateways",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Remove a specific eCommerce payment gateway",
     *     description="Disable a selected payment gateway.",
     *     operationId="removeEcommercePaymentGateway",
     *     @OA\Parameter(
     *         name="gateway",
     *         in="query",
     *         required=true,
     *         description="Name of the gateway to remove",
     *         @OA\Schema(type="string", example="paypal")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gateway removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment gateway removed")
     *         )
     *     )
     * )
     */
    public function removeEcommercePaymentGateways(Request $request)
    {
        $request->validate([
            'gateway' => 'required|string',
        ]);

        // Remove from DB/config
        // PaymentGateway::remove($request->gateway);

        return response()->json(['message' => 'Payment gateway removed'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/payments/gateways/clear",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Clear all payment gateways",
     *     description="Disables all eCommerce payment gateways.",
     *     operationId="clearEcommercePaymentGateways",
     *     @OA\Response(
     *         response=200,
     *         description="All gateways cleared",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All payment gateways cleared")
     *         )
     *     )
     * )
     */
    public function clearEcommercePaymentGateways()
    {
        // PaymentGateway::clearAll();

        return response()->json(['message' => 'All payment gateways cleared'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/google-pay",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Process a payment using Google Pay",
     *     description="Handles token verification and payment processing through Google Pay.",
     *     operationId="processGooglePay",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "amount"},
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJSUzI1NiIsInR..."),
     *             @OA\Property(property="amount", type="number", format="float", example=100.00),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="description", type="string", example="Product purchase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google Pay payment processed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction_id", type="string", example="GP_123456789"),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="amount", type="number", example=100.00),
     *                 @OA\Property(property="currency", type="string", example="USD")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid payment token or processing failed"
     *     )
     * )
     */
    public function processGooglePay(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->processGooglePay($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/paypal",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Process a payment using PayPal",
     *     description="Creates a PayPal order for payment processing.",
     *     operationId="processPayPalPayment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=100.00),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="description", type="string", example="Product purchase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PayPal order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="PayPal order created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="string", example="5O190127TN364715T"),
     *                 @OA\Property(property="status", type="string", example="created"),
     *                 @OA\Property(property="approval_url", type="string", example="https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment processing failed"
     *     )
     * )
     */
    public function processPayPalPayment(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->processPayPalPayment($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/paypal/capture",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Capture a PayPal payment",
     *     description="Captures an approved PayPal order.",
     *     operationId="capturePayPalPayment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="string", example="5O190127TN364715T")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment captured successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="PayPal payment captured successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Capture failed"
     *     )
     * )
     */
    public function capturePayPalPayment(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->capturePayPalPayment($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/stripe",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a Stripe payment intent",
     *     description="Creates a Stripe payment intent for payment processing.",
     *     operationId="createStripePaymentIntent",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=100.00),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="description", type="string", example="Product purchase")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment intent created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Stripe payment intent created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_intent_id", type="string", example="pi_123456789"),
     *                 @OA\Property(property="client_secret", type="string", example="pi_123456789_secret_..."),
     *                 @OA\Property(property="status", type="string", example="requires_payment_method"),
     *                 @OA\Property(property="amount", type="number", example=100.00),
     *                 @OA\Property(property="currency", type="string", example="USD")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment intent creation failed"
     *     )
     * )
     */
    public function createStripePaymentIntent(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->createStripePaymentIntent($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/stripe/confirm",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     summary="Confirm a Stripe payment",
     *     description="Confirms a Stripe payment intent.",
     *     operationId="confirmStripePayment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_intent_id"},
     *             @OA\Property(property="payment_intent_id", type="string", example="pi_123456789"),
     *             @OA\Property(property="payment_method_id", type="string", example="pm_123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment confirmed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Stripe payment confirmed successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Payment confirmation failed"
     *     )
     * )
     */
    public function confirmStripePayment(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->confirmStripePayment($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/stripe/webhook",
     *     tags={"Payments"},
     *     summary="Handle Stripe webhook",
     *     description="Processes Stripe webhook events for payment confirmations.",
     *     operationId="handleStripeWebhook",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             description="Stripe webhook payload"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Stripe webhook processed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Webhook processing failed"
     *     )
     * )
     */
    public function handleStripeWebhook(Request $request)
    {
        $paymentService = new \App\Services\PaymentService(
            app(\App\Http\Resources\StoreFileResource::class),
            app(\App\Http\Resources\ResponseHelper::class)
        );

        return $paymentService->handleStripeWebhook($request);
    }
}
