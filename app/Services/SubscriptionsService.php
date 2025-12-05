<?php
namespace App\Services;

use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

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


class SubscriptionsService extends BaseService
{
    //

    public function __construct(
       protected StoreFileResource $storeFileResource,
       protected ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    public function index()
    {
        $subscriptions = Subscription::with(['tier', 'coupon', 'user'])->get();
        return $this->responseHelper->respond(['subscriptions' => $subscriptions->toArray()], 'Subscriptions fetched successfully');
    }

    public function create()
    {
        $tiers = Tier::active()->get();
        $coupons = Coupon::active()->get();

        return $this->responseHelper->respond([
            'tiers' => $tiers,
            'coupons' => $coupons,
        ], 'Subscription creation data fetched successfully');
    }

    public function store($data)
    {
        $validator = Validator::make($data, [
            'tier_id' => ['required', 'integer', 'exists:tiers,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'coupon_code' => ['nullable', 'string'],
            'auto_renewal' => ['boolean'],
            'subscription_price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $tier = Tier::find($data['tier_id']);
        if (!$tier || !$tier->is_active) {
            return $this->responseHelper->error('Invalid or inactive tier', [], 400);
        }

        $coupon = null;
        $discountedPrice = $data['subscription_price'];

        if ($data['coupon_code']) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if (!$coupon || !$coupon->isValid() || !$coupon->isApplicableToTier($data['tier_id'])) {
                return $this->responseHelper->error('Invalid or inapplicable coupon', [], 400);
            }

            if (!$coupon->meetsMinimumAmount($data['subscription_price'])) {
                return $this->responseHelper->error('Minimum purchase amount not met for coupon', [
                    'minimum_amount' => $coupon->minimum_amount
                ], 400);
            }

            $discountedPrice = $coupon->calculateDiscount($data['subscription_price']);
            $discountedPrice = max(0, $data['subscription_price'] - $discountedPrice);
        }

        DB::beginTransaction();
        try {
            $subscriptionData = [
                'tier_id' => $data['tier_id'],
                'user_id' => $data['user_id'],
                'subscription_price' => $data['subscription_price'],
                'discounted_price' => $discountedPrice,
                'auto_renewal' => $data['auto_renewal'] ?? true,
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

            return $this->responseHelper->respond([
                'subscription' => $subscription->load(['tier', 'coupon', 'user'])
            ], 'Subscription created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to create subscription', ['error' => $e->getMessage()], 500);
        }
    }

    public function show(Subscription $subscription)
    {

        $subscription = $subscription->load(['tier', 'coupon', 'user']);
        return $this->responseHelper->respond([
            'subscription' =>  $subscription->toArray()
        ], 'Subscription fetched successfully');
    }

    public function edit(Subscription $subscription)
    {
        $tiers = Tier::active()->get();
        $coupons = Coupon::active()->get();

        $subscription = $subscription->load(['tier', 'coupon', 'user']);

        return $this->responseHelper->respond([
            'subscription' =>$subscription->toArray(),
            'tiers' => $tiers->toArray(),
            'coupons' => $coupons->toArray(),
        ], 'Subscription to edit fetched successfully');
    }

    public function update($data, Subscription $subscription)
    {
        $validator = Validator::make($data, [
            'tier_id' => ['nullable', 'integer', 'exists:tiers,id'],
            'auto_renewal' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        $updateData = [];

        if (isset($data['tier_id'])) {
            $oldTier = $subscription->tier;
            $tier = Tier::find($data['tier_id']);
            if (!$tier || !$tier->is_active) {
                return $this->responseHelper->error('Invalid or inactive tier', [], 400);
            }
            $updateData['tier_id'] = $data['tier_id'];
            $newTier = $tier;
        }

        if (isset($data['auto_renewal'])) {
            $updateData['auto_renewal'] = $data['auto_renewal'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        $subscription->update($updateData);

        // Fire upgrade event if tier was changed
        if (isset($oldTier) && isset($newTier) && $oldTier->id !== $newTier->id) {
            event(new SubscriptionUpgraded($subscription, $oldTier, $newTier, now()));
        }

        return $this->responseHelper->respond([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription updated successfully');
    }


    public function destroy(Subscription $subscription)
    {
        $subscription->cancel();

        // Fire subscription cancelled event
        event(new SubscriptionCancelled($subscription));

        return $this->responseHelper->respond([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription cancelled successfully');
    }


    public function extendTrial( $data, Subscription $subscription)
    {
        $validator = Validator::make($data, [
            'days' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error('Bad request', ['errors' => $validator->errors()]);
        }

        if (!$subscription->extendTrial($data['days'])) {
            return $this->responseHelper->error('Failed to extend trial', [], 400);
        }

        return $this->responseHelper->respond([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Trial extended successfully');
    }


    public function reactivate(Subscription $subscription)
    {
        if ($subscription->is_active) {
            return $this->responseHelper->error('Subscription is already active', [], 400);
        }

        if (!$subscription->reactivate()) {
            return $this->responseHelper->error('Failed to reactivate subscription', [], 400);
        }

        // Fire subscription reactivated event
        event(new SubscriptionReactivated($subscription));

        return $this->responseHelper->respond([
            'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
        ], 'Subscription reactivated successfully');
    }


    public function getUserSubscriptions($userId)
    {
        $subscriptions = Subscription::where('user_id', $userId)
            ->with(['tier', 'coupon'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->responseHelper->respond([
            'subscriptions' => $subscriptions
        ], 'User subscriptions fetched successfully');
    }


    public function getActiveSubscriptions()
    {
        $subscriptions = Subscription::active()
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->responseHelper->respond([
            'subscriptions' => $subscriptions
        ], 'Active subscriptions fetched successfully');
    }

    public function getExpiringSoon(Request $request)
    {
        $days = $request->get('days', 7);

        $subscriptions = Subscription::expiringSoon($days)
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->responseHelper->respond([
            'subscriptions' => $subscriptions
        ], 'Expiring subscriptions fetched successfully');
    }


    public function getTrialEndingSoon(Request $request)
    {
        $days = $request->get('days', 3);

        $subscriptions = Subscription::trialEndingSoon($days)
            ->with(['tier', 'coupon', 'user'])
            ->get();

        return $this->responseHelper->respond([
            'subscriptions' => $subscriptions
        ], 'Trial ending subscriptions fetched successfully');
    }


}
