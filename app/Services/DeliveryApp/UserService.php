<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function getProfile(User $user): array
    {
        try {
            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'rating' => $user->rating ?? 0,
                    'verified' => $user->hasVerifiedEmail(),
                    'accredited' => $user->accredited ?? false,
                    'subscribed' => $user->subscribed ?? false,
                    'subscription_type' => $user->subscription_type ?? null,
                    'vehicle' => $user->vehicle ?? null,
                ],
                'message' => 'Profile retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get profile error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'httpCode' => 500,
            ];
        }
    }

    public function updateProfile(User $user, array $data): array
    {
        try {
            $user->update($data);

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'message' => 'Profile updated successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Update profile error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update profile',
                'httpCode' => 500,
            ];
        }
    }

    public function updateVehicle(User $user, array $data): array
    {
        try {
            $user->update(['vehicle' => $data]);

            return [
                'success' => true,
                'message' => 'Vehicle information updated successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Update vehicle error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update vehicle information',
                'httpCode' => 500,
            ];
        }
    }
}
