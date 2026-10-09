<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function getPlans(): array
    {
        try {
            $plans = [
                [
                    'id' => 'basic',
                    'name' => 'Basic',
                    'price' => 9.99,
                    'features' => ['Basic delivery matching', 'Standard support'],
                    'billing_period' => 'monthly',
                ],
                [
                    'id' => 'premium',
                    'name' => 'Premium',
                    'price' => 19.99,
                    'features' => ['Priority delivery matching', 'Premium support', 'Analytics dashboard'],
                    'billing_period' => 'monthly',
                ],
            ];

            return [
                'success' => true,
                'data' => ['plans' => $plans],
                'message' => 'Subscription plans retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get plans error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve plans',
                'httpCode' => 500,
            ];
        }
    }

    public function subscribe(User $user, array $data): array
    {
        try {
            // Implement subscription logic here
            $user->update([
                'subscribed' => true,
                'subscription_type' => $data['plan_id'],
            ]);

            return [
                'success' => true,
                'data' => [
                    'subscription_id' => 'sub_' . $user->id,
                    'status' => 'active',
                    'current_period_end' => now()->addMonth()->toISOString(),
                ],
                'message' => 'Subscription created successfully',
                'httpCode' => 201,
            ];
        } catch (\Exception $e) {
            Log::error('Subscribe error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Subscription failed',
                'httpCode' => 500,
            ];
        }
    }

    public function getCurrent(User $user): array
    {
        try {
            if (!$user->subscribed) {
                return [
                    'success' => false,
                    'message' => 'No active subscription',
                    'httpCode' => 404,
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'subscription' => [
                        'id' => 'sub_' . $user->id,
                        'plan_id' => $user->subscription_type,
                        'status' => 'active',
                        'current_period_start' => now()->toISOString(),
                        'current_period_end' => now()->addMonth()->toISOString(),
                        'cancel_at_period_end' => false,
                    ],
                ],
                'message' => 'Current subscription retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get current subscription error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve subscription',
                'httpCode' => 500,
            ];
        }
    }

    public function cancel(User $user, array $data): array
    {
        try {
            $user->update(['subscribed' => false]);

            return [
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Cancel subscription error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Cancellation failed',
                'httpCode' => 500,
            ];
        }
    }

    public function reactivate(User $user): array
    {
        try {
            $user->update(['subscribed' => true]);

            return [
                'success' => true,
                'message' => 'Subscription reactivated successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Reactivate subscription error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Reactivation failed',
                'httpCode' => 500,
            ];
        }
    }

    public function getDetails(): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'id' => 'subscription-basic',
                    'name' => 'Basic Subscription',
                    'description' => 'Essential delivery services with basic features',
                    'price' => 1000,
                    'currency' => 'KES',
                    'features' => [
                        'Basic delivery tracking',
                        'Standard support',
                        'Mobile app access',
                        'Basic analytics',
                    ],
                    'duration' => 'Monthly',
                    'isPopular' => false,
                ],
                'message' => 'Subscription details retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get subscription details error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve details',
                'httpCode' => 500,
            ];
        }
    }
}
