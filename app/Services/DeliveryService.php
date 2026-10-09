<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryPerson;
use App\Models\DeliveryBid;
use App\Models\DeliveryEarning;
use App\Events\DeliveryStatusUpdated;
use App\Events\RiderAssigned;
use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Notifications\DeliveryAssigned;
use App\Notifications\DeliveryStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryService extends BaseService
{

    public function __construct(
        private StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }
    /**
     * Get deliveries with filtering and pagination
     */
    public function getDeliveries(array $filters = [])
    {
        try {
            $query = Delivery::with(['order', 'rider']);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['rider_id'])) {
                $query->where('rider_id', $filters['rider_id']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            $perPage = $filters['per_page'] ?? 15;

            $data = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return $this->responseHelper->respond($data->toArray(), 'Deliveries fetched successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching deliveries: ' . $e->getMessage());
            return $this->responseHelper->error('Error fetching deliveries', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new delivery
     */
    public function createDelivery(array $data)
    {
        DB::beginTransaction();
        try {
            $delivery = Delivery::create([
                'uuid' => Str::uuid(),
                'order_id' => $data['order_id'],
                'pickup_location' => $data['pickup_location'],
                'delivery_location' => $data['delivery_location'],
                'special_instructions' => $data['special_instructions'] ?? null,
                'estimated_distance' => $data['estimated_distance'] ?? null,
                'estimated_duration' => $data['estimated_duration'] ?? null,
                'fee' => $data['fee'] ?? 0,
                'ownerable_type' => $data['ownerable_type'] ?? null,
                'ownerable_id' => $data['ownerable_id'] ?? null,
            ]);

            // Calculate estimated fee if not provided
            if (!$delivery->fee && $delivery->estimated_distance) {
                $delivery->fee = $this->calculateDeliveryFee($delivery->estimated_distance);
                $delivery->save();
            }

            DB::commit();
            $data = $delivery->load(['order']);
            return $this->responseHelper->respond($data->toArray(), 'Delivery created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating delivery: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update delivery
     */
    public function updateDelivery(Delivery $delivery, array $data)
    {
        DB::beginTransaction();
        try {
            $delivery->update($data);
            DB::commit();
            $data =  $delivery->fresh(['order', 'rider']);
            return $this->responseHelper->respond($data->toArray(), 'Delivery updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating delivery: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete delivery
     */
    public function deleteDelivery(Delivery $delivery)
    {
        DB::beginTransaction();
        try {
            // Delete related bids and earnings
            $delivery->bids()->delete();
            $delivery->earnings()->delete();

            $delivery->delete();
            DB::commit();
            return $this->responseHelper->respond([], 'Delivery deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting delivery: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign rider to delivery
     */
    public function assignRider(Delivery $delivery, DeliveryPerson $rider)
    {
        DB::beginTransaction();
        try {
            $delivery->update([
                'rider_id' => $rider->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            // Create earning record
            DeliveryEarning::create([
                'uuid' => Str::uuid(),
                'delivery_id' => $delivery->id,
                'delivery_person_id' => $rider->id,
                'base_fee' => $delivery->fee,
                'total_earned' => $delivery->fee,
                'net_earnings' => $delivery->fee,
                'earning_date' => now()->toDateString(),
            ]);

            // Notify rider
            $rider->notify(new DeliveryAssigned($delivery));

            // Fire event
            event(new RiderAssigned($delivery, $rider));

            DB::commit();
            return $delivery->fresh(['rider']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning rider: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus(Delivery $delivery, string $status, string $notes = null)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $delivery->status;

            $updateData = ['status' => $status];

            // Set timestamps based on status
            switch ($status) {
                case 'picked_up':
                    $updateData['picked_up_at'] = now();
                    break;
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    break;
            }

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            $delivery->update($updateData);

            // Update rider's last delivery time
            if ($delivery->rider && in_array($status, ['picked_up', 'delivered'])) {
                $delivery->rider->update(['last_delivery_at' => now()]);
            }

            // Notify relevant parties
            if ($delivery->order && $delivery->order->customer) {
                $delivery->order->customer->notify(new DeliveryStatusChanged($delivery, $oldStatus, $status));
            }

            // Fire event
            event(new DeliveryStatusUpdated($delivery, $oldStatus, $status));

            DB::commit();
            return $delivery->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating delivery status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available riders for delivery
     */
    public function getAvailableRiders(Delivery $delivery, array $locationData = [])
    {
        $latitude = $locationData['latitude'] ?? $delivery->pickup_location['latitude'] ?? null;
        $longitude = $locationData['longitude'] ?? $delivery->pickup_location['longitude'] ?? null;
        $radius = $locationData['radius'] ?? 10;

        if (!$latitude || !$longitude) {
            return collect();
        }

        return DeliveryPerson::active()
            ->online()
            ->available()
            ->verified()
            ->accredited()
            ->nearLocation($latitude, $longitude, $radius)
            ->with(['primaryVehicle'])
            ->get()
            ->filter(function ($rider) use ($delivery) {
                return $rider->canAcceptDelivery($delivery);
            });
    }

    /**
     * Submit rating and feedback
     */
    public function submitRating(Delivery $delivery, array $data)
    {
        DB::beginTransaction();
        try {
            $delivery->update([
                'rating' => $data['rating'],
                'rider_rating' => $data['rider_rating'] ?? null,
                'food_rating' => $data['food_rating'] ?? null,
                'packaging_rating' => $data['packaging_rating'] ?? null,
                'tip_amount' => $data['tip_amount'] ?? 0,
                'feedback' => $data['feedback'] ?? null,
            ]);

            // Update rider rating
            if ($delivery->rider && isset($data['rider_rating'])) {
                $delivery->rider->updateRating($data['rider_rating']);
            }

            // Update earnings with tip
            if (isset($data['tip_amount']) && $data['tip_amount'] > 0) {
                $earning = $delivery->earnings()->first();
                if ($earning) {
                    $earning->update([
                        'tip_amount' => $data['tip_amount'],
                        'total_earned' => $earning->total_earned + $data['tip_amount'],
                        'net_earnings' => $earning->net_earnings + $data['tip_amount'],
                    ]);
                }
            }

            DB::commit();
            return $delivery->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting rating: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get delivery statistics
     */
    public function getDeliveryStatistics(array $filters = [])
    {
        $query = Delivery::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $totalDeliveries = $query->count();
        $completedDeliveries = (clone $query)->where('status', 'delivered')->count();
        $completionRate = $totalDeliveries > 0 ? ($completedDeliveries / $totalDeliveries) * 100 : 0;

        $averageRating = (clone $query)->whereNotNull('rating')->avg('rating');
        $totalRevenue = (clone $query)->sum('fee');

        $statusBreakdown = (clone $query)->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total_deliveries' => $totalDeliveries,
            'completed_deliveries' => $completedDeliveries,
            'completion_rate' => round($completionRate, 2),
            'average_rating' => round($averageRating ?? 0, 2),
            'total_revenue' => $totalRevenue,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    /**
     * Calculate delivery fee based on distance
     */
    private function calculateDeliveryFee(float $distance): float
    {
        // Base fee + distance-based fee
        $baseFee = 50; // KES
        $perKmFee = 20; // KES per km

        return $baseFee + ($distance * $perKmFee);
    }

    /**
     * Send delivery request to available riders
     */
    public function sendDeliveryRequest(Delivery $delivery)
    {
        $availableRiders = $this->getAvailableRiders($delivery);

        foreach ($availableRiders as $rider) {
            // Create bid
            DeliveryBid::create([
                'uuid' => Str::uuid(),
                'delivery_id' => $delivery->id,
                'delivery_person_id' => $rider->id,
                'bid_amount' => $delivery->fee,
                'estimated_arrival_time' => rand(10, 30), // Mock ETA in minutes
                'expires_at' => now()->addMinutes(5),
            ]);

            // Notify rider
            // Implementation depends on notification system
        }

        return $availableRiders->count();
    }
}
