<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\Coupon;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionReactivated;
use App\Events\SubscriptionUpgraded;
use App\Services\SubscriptionsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Contracts\Service\Attribute\SubscribedService;

class SubscriptionController extends BaseController
{

     public function __construct(
        ApiResource $apiResource,
        private readonly SubscriptionsService $subscriptionService
    ) {
        parent::__construct($apiResource);
    }
    /**
     * @OA\Info(
     *     title="Dukaverse API",
     *     version="1.0.0",
     *     description="API documentation for managing Subscriptions"
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/subscriptions",
     *     summary="Get all subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Subscriptions fetched successfully")
     * )
     */
    public function index()
    {
        try {
            $result = $this->subscriptionService->index();
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/create",
     *     summary="Get subscription creation metadata",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Subscription creation metadata fetched successfully")
     * )
     */
    public function create()
    {
        try {
            $result = $this->subscriptionService->create();
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *     summary="Create a new subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tier_id","user_id"},
     *             @OA\Property(property="tier_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="coupon_code", type="string"),
     *             @OA\Property(property="auto_renewal", type="boolean"),
     *             @OA\Property(property="subscription_price", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Subscription created successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        try {
            $result = $this->subscriptionService->store($request->all());
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/{id}",
     *     summary="Get a subscription by ID",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Subscription fetched successfully"),
     *     @OA\Response(response=404, description="Subscription not found")
     * )
     */
    public function show(Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->show($subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/{id}/edit",
     *     summary="Get a subscription for editing",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Subscription to edit fetched successfully")
     * )
     */
    public function edit(Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->edit($subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/subscriptions/{id}",
     *     summary="Update a subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tier_id", type="integer"),
     *             @OA\Property(property="auto_renewal", type="boolean"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Subscription updated successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function update(Request $request, Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->update($request->all(), $subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/subscriptions/{id}",
     *     summary="Cancel a subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Subscription cancelled successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function destroy(Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->destroy($subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions/{id}/extend-trial",
     *     summary="Extend trial period",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"days"},
     *             @OA\Property(property="days", type="integer", minimum=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Trial extended successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function extendTrial(Request $request, Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->extendTrial($request->all(), $subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions/{id}/reactivate",
     *     summary="Reactivate a cancelled subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Subscription reactivated successfully"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function reactivate(Subscription $subscription)
    {
        try {
            $result = $this->subscriptionService->reactivate($subscription);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/user/{userId}",
     *     summary="Get subscriptions for a specific user",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="User subscriptions fetched successfully")
     * )
     */
    public function getUserSubscriptions($userId)
    {
        try {
            $result = $this->subscriptionService->getUserSubscriptions($userId);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/active",
     *     summary="Get all active subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Active subscriptions fetched successfully")
     * )
     */
    public function getActiveSubscriptions()
    {
        try {
            $result = $this->subscriptionService->getActiveSubscriptions();
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/expiring-soon",
     *     summary="Get subscriptions expiring soon",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer", default=7)),
     *     @OA\Response(response=200, description="Expiring subscriptions fetched successfully")
     * )
     */
    public function getExpiringSoon(Request $request)
    {
        try {
            $result = $this->subscriptionService->getExpiringSoon($request);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/trial-ending-soon",
     *     summary="Get subscriptions with trial ending soon",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer", default=3)),
     *     @OA\Response(response=200, description="Trial ending subscriptions fetched successfully")
     * )
     */
    public function getTrialEndingSoon(Request $request)
    {
        try {
            $result = $this->subscriptionService->getTrialEndingSoon($request);
            if (isset($result["error"]))
                return $this->apiResource->error($result['message'], $result["httpCode"], $result["error"]);

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server error", 500, ["error" => $ex->getMessage()]);
        }
    }
}
