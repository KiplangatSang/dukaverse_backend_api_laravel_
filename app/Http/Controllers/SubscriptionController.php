<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\Coupon;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionReactivated;
use App\Events\SubscriptionUpgraded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends BaseController
{
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
        $subscriptions = Subscription::with(['tier', 'coupon', 'user'])->get();
        return $this->sendResponse(['subscriptions' => $subscriptions], 'Subscriptions fetched successfully');
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
        $tiers = Tier::active()->get();
        $coupons = Coupon::active()->get();

        return $this->sendResponse([
            'tiers' => $tiers,
            'coupons' => $coupons,
        ], 'Subscription creation data fetched successfully');
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
        $validator = Validator::make($request->all(), [
            'tier_id' => ['required', 'integer', 'exists:tiers,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'coupon_code' => ['nullable', 'string'],
            'auto_renewal' => ['boolean'],
            'subscription_price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $tier = Tier::find($request->tier_id);
        if (!$tier || !$tier->is_active) {
            return $this->sendError('Invalid or inactive tier', [], 400);
        }

        $coupon = null;
        $discountedPrice = $request->subscription_price;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            if (!$coupon || !$coupon->isValid() || !$coupon->isApplicableToTier($request->tier_id)) {
                return $this->sendError('Invalid or inapplicable coupon', [], 400);
            }

            if (!$coupon->meetsMinimumAmount($request->subscription_price)) {
                return $this->sendError('Minimum purchase amount not met for coupon', [
                    'minimum_amount' => $coupon->minimum_amount
                ], 400);
            }

            $discountedPrice = $coupon->calculateDiscount($request->subscription_price);
            $discountedPrice = max(0, $request->subscription_price - $discountedPrice);
        }

        DB::beginTransaction();
        try {
            $subscriptionData = [
                'tier_id' => $request->tier_id,
                'user_id' => $request->user_id,
                'subscription_price' => $request->subscription_price,
                'discounted_price' => $discountedPrice,
                'auto_renewal' => $request->auto_renewal ?? true,
                'is_active' => true,
                'trial_end_date' => $tier->hasTrial() ? $tier->getTrialEndDate() : null,
                'expires_at' => $tier->hasTrial() ? null : now()->addDays($tier->getBillingIntervalDays()),
            ];

            if ($coupon) {
                $subscriptionData['coupon_id'] = $coupon->id;
                $coupon->incrementUsage();
            }

            $subscription = Subscription::create($subscriptionData);

            DB::commit();

            // Fire subscription created event
            event(new SubscriptionCreated($subscription));

            return $this->sendResponse([
                'subscription' => $subscription->load(['tier', 'coupon', 'user'])
            ], 'Subscription created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create subscription', ['error' => $e->getMessage()], 500);
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
        return $this->sendResponse([
            'subscription' => $subscription->load(['tier', 'coupon', 'user'])
        ], 'Subscription fetched successfully');
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
        $tiers = Tier::active()->get();
        $coupons = Coupon::active()->get();

        return $this->sendResponse([
            'subscription' => $subscription->load(['tier', 'coupon', 'user']),
            'tiers' => $tiers,
            'coupons' => $coupons,
        ], 'Subscription to edit fetched successfully');
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
        $validator = Validator::make($request->all(), [
            'tier_id' => ['nullable', 'integer', 'exists:tiers,id'],
            'auto_renewal' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        $updateData = [];

        if ($request->has('tier_id')) {
            $oldTier = $subscription->tier;
            $tier = Tier::find($request->tier_id);
            if (!$tier || !$tier->is_active) {
                return $this->sendError('Invalid or inactive tier', [], 400);
            }
            $updateData['tier_id'] = $request->tier_id;
            $newTier = $tier;
        }

        if ($request->has('auto_renewal')) {
            $updateData['auto_renewal'] = $request->auto_renewal;
        }

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->is_active;
        }

        $subscription->update($updateData);

        // Fire upgrade event if tier was changed
        if (isset($oldTier) && isset($newTier) && $oldTier->id !== $newTier->id) {
            event(new SubscriptionUpgraded($subscription, $oldTier, $newTier, now()));
        }

        return $this->sendResponse([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription updated successfully');
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
        $subscription->cancel();

        // Fire subscription cancelled event
        event(new SubscriptionCancelled($subscription));

        return $this->sendResponse([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription cancelled successfully');
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
        $validator = Validator::make($request->all(), [
            'days' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', ['errors' => $validator->errors()]);
        }

        if (!$subscription->extendTrial($request->days)) {
            return $this->sendError('Failed to extend trial', [], 400);
        }

        return $this->sendResponse([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Trial extended successfully');
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
        if ($subscription->is_active) {
            return $this->sendError('Subscription is already active', [], 400);
        }

        if (!$subscription->reactivate()) {
            return $this->sendError('Failed to reactivate subscription', [], 400);
        }

        // Fire subscription reactivated event
        event(new SubscriptionReactivated($subscription));

        return $this->sendResponse([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription reactivated successfully');
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
        $subscriptions = Subscription::where('user_id', $userId)
            ->with(['tier', 'coupon'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'subscriptions' => $subscriptions
        ], 'User subscriptions fetched successfully');
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
        $subscriptions = Subscription::active()
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->sendResponse([
            'subscriptions' => $subscriptions
        ], 'Active subscriptions fetched successfully');
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
        $days = $request->get('days', 7);

        $subscriptions = Subscription::expiringSoon($days)
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->sendResponse([
            'subscriptions' => $subscriptions
        ], 'Expiring subscriptions fetched successfully');
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
        $days = $request->get('days', 3);

        $subscriptions = Subscription::trialEndingSoon($days)
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->sendResponse([
            'subscriptions' => $subscriptions
        ], 'Trial ending subscriptions fetched successfully');
    }
}
