<?php
namespace App\Services;

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
    public function index()
    {
        try {
            $subscriptions = Subscription::with(['tier', 'coupon', 'user'])->get();
            return [
                'data' => ['subscriptions' => $subscriptions->toArray()],
                'httpCode' => 200,
                'message' => 'Subscriptions fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch subscriptions'
            ];
        }
    }

    public function create()
    {
        try {
            $tiers = Tier::active()->get();
            $coupons = Coupon::active()->get();

            return [
                'data' => [
                    'tiers' => $tiers,
                    'coupons' => $coupons,
                ],
                'httpCode' => 200,
                'message' => 'Subscription creation data fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch subscription creation data'
            ];
        }
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
            return [
                'error' => $validator->errors(),
                'httpCode' => 400,
                'message' => 'Bad request'
            ];
        }

        $tier = Tier::find($data['tier_id']);
        if (!$tier || !$tier->is_active) {
            return [
                'error' => [],
                'httpCode' => 400,
                'message' => 'Invalid or inactive tier'
            ];
        }

        $coupon = null;
        $discountedPrice = $data['subscription_price'];

        if ($data['coupon_code']) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();
            if (!$coupon || !$coupon->isValid() || !$coupon->isApplicableToTier($data['tier_id'])) {
                return [
                    'error' => [],
                    'httpCode' => 400,
                    'message' => 'Invalid or inapplicable coupon'
                ];
            }

            if (!$coupon->meetsMinimumAmount($data['subscription_price'])) {
                return [
                    'error' => ['minimum_amount' => $coupon->minimum_amount],
                    'httpCode' => 400,
                    'message' => 'Minimum purchase amount not met for coupon'
                ];
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

            return [
                'data' => [
                    'subscription' => $subscription->load(['tier', 'coupon', 'user'])
                ],
                'httpCode' => 200,
                'message' => 'Subscription created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to create subscription'
            ];
        }
    }

    public function show(Subscription $subscription)
    {
        try {
            $subscription = $subscription->load(['tier', 'coupon', 'user']);
            return [
                'data' => [
                    'subscription' => $subscription->toArray()
                ],
                'httpCode' => 200,
                'message' => 'Subscription fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch subscription'
            ];
        }
    }

    public function edit(Subscription $subscription)
    {
        try {
            $tiers = Tier::active()->get();
            $coupons = Coupon::active()->get();

            $subscription = $subscription->load(['tier', 'coupon', 'user']);

            return [
                'data' => [
                    'subscription' => $subscription->toArray(),
                    'tiers' => $tiers->toArray(),
                    'coupons' => $coupons->toArray(),
                ],
                'httpCode' => 200,
                'message' => 'Subscription to edit fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch subscription edit data'
            ];
        }
    }

    public function update($data, Subscription $subscription)
    {
        $validator = Validator::make($data, [
            'tier_id' => ['nullable', 'integer', 'exists:tiers,id'],
            'auto_renewal' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return [
                'error' => $validator->errors(),
                'httpCode' => 400,
                'message' => 'Bad request'
            ];
        }

        try {
            $updateData = [];

            if (isset($data['tier_id'])) {
                $oldTier = $subscription->tier;
                $tier = Tier::find($data['tier_id']);
                if (!$tier || !$tier->is_active) {
                    return [
                        'error' => [],
                        'httpCode' => 400,
                        'message' => 'Invalid or inactive tier'
                    ];
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

            return [
                'data' => [
                    'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
                ],
                'httpCode' => 200,
                'message' => 'Subscription updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to update subscription'
            ];
        }
    }


    public function destroy(Subscription $subscription)
    {
        try {
            $subscription->cancel();

            // Fire subscription cancelled event
            event(new SubscriptionCancelled($subscription));

            return [
                'data' => [
                    'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
                ],
                'httpCode' => 200,
                'message' => 'Subscription cancelled successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to cancel subscription'
            ];
        }
    }


    public function extendTrial( $data, Subscription $subscription)
    {
        $validator = Validator::make($data, [
            'days' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return [
                'error' => $validator->errors(),
                'httpCode' => 400,
                'message' => 'Bad request'
            ];
        }

        try {
            if (!$subscription->extendTrial($data['days'])) {
                return [
                    'error' => [],
                    'httpCode' => 400,
                    'message' => 'Failed to extend trial'
                ];
            }

            return [
                'data' => [
                    'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
                ],
                'httpCode' => 200,
                'message' => 'Trial extended successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to extend trial'
            ];
        }
    }


    public function reactivate(Subscription $subscription)
    {
        try {
            if ($subscription->is_active) {
                return [
                    'error' => [],
                    'httpCode' => 400,
                    'message' => 'Subscription is already active'
                ];
            }

            if (!$subscription->reactivate()) {
                return [
                    'error' => [],
                    'httpCode' => 400,
                    'message' => 'Failed to reactivate subscription'
                ];
            }

            // Fire subscription reactivated event
            event(new SubscriptionReactivated($subscription));

            return [
                'data' => [
                    'subscription' => $subscription->fresh(['tier', 'coupon', 'user'])
                ],
                'httpCode' => 200,
                'message' => 'Subscription reactivated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to reactivate subscription'
            ];
        }
    }


    public function getUserSubscriptions($userId)
    {
        try {
            $subscriptions = Subscription::where('user_id', $userId)
                ->with(['tier', 'coupon'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'data' => [
                    'subscriptions' => $subscriptions
                ],
                'httpCode' => 200,
                'message' => 'User subscriptions fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch user subscriptions'
            ];
        }
    }


    public function getActiveSubscriptions()
    {
        try {
            $subscriptions = Subscription::active()
                ->with(['tier', 'coupon', 'user'])
                ->get();

            return [
                'data' => [
                    'subscriptions' => $subscriptions
                ],
                'httpCode' => 200,
                'message' => 'Active subscriptions fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch active subscriptions'
            ];
        }
    }

    public function getExpiringSoon(Request $request)
    {
        try {
            $days = $request->get('days', 7);

            $subscriptions = Subscription::expiringSoon($days)
                ->with(['tier', 'coupon', 'user'])
                ->get();

            return [
                'data' => [
                    'subscriptions' => $subscriptions
                ],
                'httpCode' => 200,
                'message' => 'Expiring subscriptions fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch expiring subscriptions'
            ];
        }
    }


    public function getTrialEndingSoon(Request $request)
    {
        try {
            $days = $request->get('days', 3);

            $subscriptions = Subscription::trialEndingSoon($days)
                ->with(['tier', 'coupon', 'user'])
                ->get();

            return [
                'data' => [
                    'subscriptions' => $subscriptions
                ],
                'httpCode' => 200,
                'message' => 'Trial ending subscriptions fetched successfully'
            ];
        } catch (\Exception $e) {
            return [
                'error' => ['error' => $e->getMessage()],
                'httpCode' => 500,
                'message' => 'Failed to fetch trial ending subscriptions'
            ];
        }
    }


}
