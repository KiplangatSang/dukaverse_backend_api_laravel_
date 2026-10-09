<?php

namespace App\Http\Controllers\DeliveryApp;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ApiResource;
use App\Services\DeliveryApp\SubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends BaseController
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscriptions/plans",
     *     summary="Get available subscription plans",
     *     tags={"Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Plans retrieved successfully"
     *     )
     * )
     */
    public function plans()
    {
        try {
            $result = $this->subscriptionService->getPlans();
            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/subscribe",
     *     summary="Subscribe to a plan",
     *     tags={"Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"plan_id","payment_method"},
     *             @OA\Property(property="plan_id", type="string"),
     *             @OA\Property(property="payment_method", type="string", enum={"card","paypal","apple_pay","google_pay"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription created successfully"
     *     )
     * )
     */
    public function subscribe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'plan_id' => 'required|string|in:basic,premium',
                'payment_method' => 'required|string|in:card,paypal,apple_pay,google_pay',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->subscriptionService->subscribe($request->user(), $request->all());

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
     *     path="/api/v1/subscriptions/current",
     *     summary="Get current subscription",
     *     tags={"Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current subscription retrieved successfully"
     *     )
     * )
     */
    public function current(Request $request)
    {
        try {
            $result = $this->subscriptionService->getCurrent($request->user());

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
     *     path="/api/v1/subscriptions/cancel",
     *     summary="Cancel subscription",
     *     tags={"Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cancel_at_period_end", type="boolean", default=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully"
     *     )
     * )
     */
    public function cancel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cancel_at_period_end' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->apiResource->error("Validation Error", 422, $validator->errors());
            }

            $result = $this->subscriptionService->cancel($request->user(), $request->all());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success(null, $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/reactivate",
     *     summary="Reactivate cancelled subscription",
     *     tags={"Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Subscription reactivated successfully"
     *     )
     * )
     */
    public function reactivate(Request $request)
    {
        try {
            $result = $this->subscriptionService->reactivate($request->user());

            if (!$result['success']) {
                return $this->apiResource->error($result['message'], $result['httpCode']);
            }

            return $this->apiResource->success(null, $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription/details",
     *     summary="Get subscription service details",
     *     tags={"Service Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Subscription details retrieved successfully"
     *     )
     * )
     */
    public function details()
    {
        try {
            $result = $this->subscriptionService->getDetails();
            return $this->apiResource->success($result['data'], $result['message'], $result['httpCode']);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
        }
    }
}
