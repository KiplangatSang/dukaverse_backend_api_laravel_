<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/process",
     *     summary="Process a payment",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","payment_method","amount"},
     *             @OA\Property(property="type", type="string", enum={"verification","accreditation","subscription"}),
     *             @OA\Property(property="payment_method", type="string", enum={"card","paypal","apple_pay","google_pay"}),
     *             @OA\Property(property="amount", type="number", minimum=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment initiated successfully"
     *     )
     * )
     */
    public function process(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:verification,accreditation,subscription',
                'payment_method' => 'required|string|in:card,paypal,apple_pay,google_pay',
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->paymentService->processPayment($request->user(), $request->all());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments/status/{paymentId}",
     *     summary="Check payment status",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status retrieved successfully"
     *     )
     * )
     */
    public function status(Request $request, $paymentId)
    {
        try {
            $result = $this->paymentService->getPaymentStatus($request->user(), $paymentId);

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments/history",
     *     summary="Get payment history",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Payment history retrieved successfully"
     *     )
     * )
     */
    public function history(Request $request)
    {
        try {
            $result = $this->paymentService->getPaymentHistory($request->user());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/webhook",
     *     summary="Stripe webhook for payment confirmations",
     *     tags={"Payment"},
     *     @OA\RequestBody(
     *         description="Stripe webhook payload",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     )
     * )
     */
    public function webhook(Request $request)
    {
        try {
            $result = $this->paymentService->handleWebhook($request->all(), $request->header('Stripe-Signature'));

            if (!$result['success']) {
                return response()->json(['error' => $result['message']], $result['httpCode']);
            }
            return response()->json(['status' => 'success'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
