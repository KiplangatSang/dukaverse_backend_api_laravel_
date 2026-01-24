<?php

namespace App\Services\DeliveryApp;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class DeliveryAppService
{
    public function getAvailableJobs(User $user, array $params): array
    {
        try {
            // Implement available jobs logic here
            $jobs = [
                [
                    'id' => 'job_123',
                    'pickup_location' => [
                        'lat' => -1.2864,
                        'lng' => 36.8172,
                        'address' => 'Westlands, Nairobi',
                    ],
                    'delivery_location' => [
                        'lat' => -1.2921,
                        'lng' => 36.8219,
                        'address' => 'Kilimani, Nairobi',
                    ],
                    'package_details' => [
                        'type' => 'food',
                        'weight' => '2kg',
                        'value' => 1500,
                    ],
                    'estimated_distance' => '5.2km',
                    'estimated_time' => '25min',
                    'base_fare' => 300,
                    'created_at' => now()->toISOString(),
                ],
            ];

            return [
                'success' => true,
                'data' => ['jobs' => $jobs],
                'message' => 'Available jobs retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get available jobs error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve available jobs',
                'httpCode' => 500,
            ];
        }
    }

    public function placeBid(User $user, string $jobId, array $data): array
    {
        try {
            // Get delivery person and their primary vehicle
            $deliveryPerson = $user->deliveryPerson;
            if (!$deliveryPerson) {
                return [
                    'success' => false,
                    'message' => 'Delivery person profile not found',
                    'httpCode' => 404,
                ];
            }

            $primaryVehicle = $deliveryPerson->vehicles()->primary()->verified()->first();
            if (!$primaryVehicle) {
                return [
                    'success' => false,
                    'message' => 'No verified primary vehicle found',
                    'httpCode' => 404,
                ];
            }

            // Find the delivery
            $delivery = Delivery::where('uuid', $jobId)->first();
            if (!$delivery) {
                return [
                    'success' => false,
                    'message' => 'Delivery job not found',
                    'httpCode' => 404,
                ];
            }

            // Check if delivery is still available
            if (!$delivery->isAvailableForBidding()) {
                return [
                    'success' => false,
                    'message' => 'Delivery job is no longer available for bidding',
                    'httpCode' => 400,
                ];
            }

            // Check vehicle compatibility
            if (!$delivery->canBeHandledByVehicle($primaryVehicle->vehicle_type)) {
                return [
                    'success' => false,
                    'message' => 'Your vehicle type is not compatible with this delivery category',
                    'httpCode' => 400,
                ];
            }

            // Check if user already bid on this delivery
            $existingBid = $delivery->bids()->where('delivery_person_id', $deliveryPerson->id)->first();
            if ($existingBid) {
                return [
                    'success' => false,
                    'message' => 'You have already placed a bid on this delivery',
                    'httpCode' => 400,
                ];
            }

            // Create the bid
            $bid = $delivery->bids()->create([
                'uuid' => 'bid_' . time() . '_' . $deliveryPerson->id,
                'delivery_person_id' => $deliveryPerson->id,
                'bid_amount' => $data['bid_amount'],
                'estimated_arrival_time' => $data['estimated_time'],
                'notes' => $data['notes'] ?? null,
                'expires_at' => now()->addHours(2), // Bids expire in 2 hours
            ]);

            return [
                'success' => true,
                'data' => [
                    'bid_id' => $bid->uuid,
                    'job_id' => $jobId,
                    'bid_amount' => $bid->bid_amount,
                    'estimated_time' => $bid->estimated_arrival_time,
                    'status' => $bid->status,
                    'placed_at' => $bid->created_at->toISOString(),
                ],
                'message' => 'Bid placed successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Place bid error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to place bid',
                'httpCode' => 500,
            ];
        }
    }

    public function getMyBids(User $user): array
    {
        try {
            // Implement my bids logic here
            $bids = [
                [
                    'id' => 'bid_123',
                    'job_id' => 'job_123',
                    'bid_amount' => 350,
                    'estimated_time' => 30,
                    'status' => 'pending',
                    'placed_at' => now()->toISOString(),
                ],
            ];

            return [
                'success' => true,
                'data' => ['bids' => $bids],
                'message' => 'Active bids retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get my bids error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve bids',
                'httpCode' => 500,
            ];
        }
    }

    public function acceptJob(User $user, string $jobId): array
    {
        try {
            // Implement job acceptance logic here
            return [
                'success' => true,
                'message' => 'Job accepted successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Accept job error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to accept job',
                'httpCode' => 500,
            ];
        }
    }

    public function updateStatus(User $user, string $jobId, string $status): array
    {
        try {
            // Implement status update logic here
            return [
                'success' => true,
                'message' => 'Status updated successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Update status error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update status',
                'httpCode' => 500,
            ];
        }
    }

    public function getHistory(User $user): array
    {
        try {
            // Implement delivery history logic here
            $deliveries = [
                [
                    'id' => 'del_123',
                    'job_id' => 'job_123',
                    'status' => 'completed',
                    'pickup_location' => 'Westlands, Nairobi',
                    'delivery_location' => 'Kilimani, Nairobi',
                    'amount_earned' => 350,
                    'completed_at' => now()->toISOString(),
                ],
            ];

            return [
                'success' => true,
                'data' => ['deliveries' => $deliveries],
                'message' => 'Delivery history retrieved successfully',
                'httpCode' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Get history error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve history',
                'httpCode' => 500,
            ];
        }
    }
}
