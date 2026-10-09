<?php
namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Order;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Exception;

class KitchenService extends BaseService
{
    public function __construct(
        StoreFileResource $storeFileResource,
        private ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }

    /**
     * Get kitchen orders with filtering
     */
    public function getKitchenOrders($filters = [])
    {
        try {
            $query = Order::with(['items', 'customer', 'table'])
                         ->whereIn('status', ['confirmed', 'preparing', 'ready'])
                         ->whereIn('order_type', ['dine_in', 'takeaway']);

            // Apply filters
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['station'])) {
                // Filter by station based on menu items
                $query->whereHas('items', function($q) use ($filters) {
                    $q->where('station', $filters['station']);
                });
            }

            if (isset($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }

            // Order by priority and creation time
            $query->orderByRaw("
                CASE
                    WHEN priority = 'rush' THEN 1
                    WHEN priority = 'urgent' THEN 2
                    WHEN priority = 'normal' THEN 3
                    ELSE 4
                END
            ")->orderBy('created_at', 'asc');

            $orders = $query->paginate($filters['limit'] ?? 50);

            // Transform orders for kitchen display
            $kitchenOrders = $orders->map(function($order) {
                return $this->transformKitchenOrder($order);
            });

            return $this->responseHelper->respond($kitchenOrders, 'Kitchen orders retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve kitchen orders', 500, [$e->getMessage()]);
        }
    }

    /**
     * Update item status in kitchen
     */
    public function updateItemStatus($orderId, $itemId, $data)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($orderId);

            // Find the specific item in the order
            $orderItems = json_decode($order->items, true) ?? [];
            $itemFound = false;

            foreach ($orderItems as &$item) {
                if ($item['id'] == $itemId) {
                    $item['status'] = $data['status'];
                    $item['station'] = $data['station'] ?? $item['station'];
                    $item['assigned_to'] = $data['assigned_to'] ?? $item['assigned_to'];
                    $item['notes'] = $data['notes'] ?? $item['notes'];

                    // Update timestamps
                    if ($data['status'] === 'preparing' && !isset($item['started_at'])) {
                        $item['started_at'] = now()->toISOString();
                    } elseif ($data['status'] === 'ready' && !isset($item['completed_at'])) {
                        $item['completed_at'] = now()->toISOString();
                    }

                    $itemFound = true;
                    break;
                }
            }

            if (!$itemFound) {
                throw new Exception('Item not found in order');
            }

            // Update the order items
            $order->update(['items' => json_encode($orderItems)]);

            // Check if all items are ready
            $allItemsReady = collect($orderItems)->every(function($item) {
                return $item['status'] === 'ready';
            });

            if ($allItemsReady && $order->status !== 'ready') {
                $order->update(['status' => 'ready']);
            }

            DB::commit();

            return $this->responseHelper->respond(
                $this->transformKitchenOrder($order->fresh()),
                'Item status updated successfully'
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseHelper->error('Failed to update item status', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get kitchen stations
     */
    public function getKitchenStations()
    {
        try {
            // This would typically come from a stations configuration
            // For now, return predefined stations
            $stations = [
                [
                    'id' => 'grill',
                    'name' => 'Grill Station',
                    'code' => 'grill',
                    'capacity' => 10,
                    'current_load' => 7,
                    'status' => 'active',
                    'assigned_staff' => ['chef1', 'chef2'],
                    'equipment' => ['grill', 'fryer', 'oven'],
                    'specializations' => ['burgers', 'steaks', 'chicken']
                ],
                [
                    'id' => 'salad',
                    'name' => 'Salad Station',
                    'code' => 'salad',
                    'capacity' => 5,
                    'current_load' => 3,
                    'status' => 'active',
                    'assigned_staff' => ['chef3'],
                    'equipment' => ['prep_tables', 'refrigerator'],
                    'specializations' => ['salads', 'cold_dishes']
                ],
                [
                    'id' => 'dessert',
                    'name' => 'Dessert Station',
                    'code' => 'dessert',
                    'capacity' => 3,
                    'current_load' => 2,
                    'status' => 'active',
                    'assigned_staff' => ['chef4'],
                    'equipment' => ['oven', 'mixer'],
                    'specializations' => ['cakes', 'ice_cream', 'pastries']
                ],
                [
                    'id' => 'beverages',
                    'name' => 'Beverage Station',
                    'code' => 'beverages',
                    'capacity' => 4,
                    'current_load' => 1,
                    'status' => 'active',
                    'assigned_staff' => ['barista1'],
                    'equipment' => ['espresso_machine', 'blender'],
                    'specializations' => ['coffee', 'smoothies', 'cocktails']
                ]
            ];

            return $this->responseHelper->respond($stations, 'Kitchen stations retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve kitchen stations', 500, [$e->getMessage()]);
        }
    }

    /**
     * Get kitchen performance metrics
     */
    public function getKitchenPerformance($filters = [])
    {
        try {
            $query = Order::whereIn('status', ['completed'])
                         ->whereIn('order_type', ['dine_in', 'takeaway']);

            // Apply date filters
            if (isset($filters['period'])) {
                switch ($filters['period']) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereMonth('created_at', now()->month);
                        break;
                }
            }

            $orders = $query->with('items')->get();

            $performance = [
                'average_preparation_time' => $this->calculateAveragePreparationTime($orders),
                'on_time_completion_rate' => $this->calculateOnTimeCompletionRate($orders),
                'rush_order_completion_rate' => $this->calculateRushOrderCompletionRate($orders),
                'peak_hour_performance' => $this->calculatePeakHourPerformance($orders),
                'station_performance' => $this->calculateStationPerformance($orders, $filters['station'] ?? null),
            ];

            return $this->responseHelper->respond($performance, 'Kitchen performance metrics retrieved successfully');
        } catch (Exception $e) {
            return $this->responseHelper->error('Failed to retrieve kitchen performance', 500, [$e->getMessage()]);
        }
    }

    /**
     * Transform order for kitchen display
     */
    private function transformKitchenOrder($order)
    {
        $orderItems = json_decode($order->items, true) ?? [];

        return [
            'id' => $order->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'order_type' => $order->order_type,
            'table_number' => $order->table?->number,
            'customer_name' => $order->customer?->name ?? 'Walk-in',
            'items' => collect($orderItems)->map(function($item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'customizations' => $item['customizations'] ?? [],
                    'special_instructions' => $item['special_instructions'] ?? '',
                    'status' => $item['status'] ?? 'pending',
                    'station' => $item['station'] ?? 'general',
                    'estimated_time' => $item['estimated_time'] ?? 15,
                    'priority' => $item['priority'] ?? 'normal',
                    'assigned_to' => $item['assigned_to'] ?? null,
                    'started_at' => $item['started_at'] ?? null,
                    'completed_at' => $item['completed_at'] ?? null,
                ];
            }),
            'priority' => $order->priority ?? 'normal',
            'created_at' => $order->created_at,
            'estimated_completion' => $order->estimated_ready_time,
        ];
    }

    /**
     * Calculate average preparation time
     */
    private function calculateAveragePreparationTime($orders)
    {
        $totalTime = 0;
        $count = 0;

        foreach ($orders as $order) {
            $orderItems = json_decode($order->items, true) ?? [];
            foreach ($orderItems as $item) {
                if (isset($item['started_at']) && isset($item['completed_at'])) {
                    $startTime = strtotime($item['started_at']);
                    $endTime = strtotime($item['completed_at']);
                    $totalTime += ($endTime - $startTime) / 60; // Convert to minutes
                    $count++;
                }
            }
        }

        return $count > 0 ? round($totalTime / $count, 1) : 0;
    }

    /**
     * Calculate on-time completion rate
     */
    private function calculateOnTimeCompletionRate($orders)
    {
        $onTime = 0;
        $total = 0;

        foreach ($orders as $order) {
            $orderItems = json_decode($order->items, true) ?? [];
            foreach ($orderItems as $item) {
                if (isset($item['completed_at']) && isset($item['estimated_time'])) {
                    $completionTime = strtotime($item['completed_at']);
                    $startTime = strtotime($item['started_at'] ?? $order->created_at);
                    $actualTime = ($completionTime - $startTime) / 60; // minutes

                    if ($actualTime <= $item['estimated_time']) {
                        $onTime++;
                    }
                    $total++;
                }
            }
        }

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
    }

    /**
     * Calculate rush order completion rate
     */
    private function calculateRushOrderCompletionRate($orders)
    {
        $rushOrders = $orders->filter(function($order) {
            return $order->priority === 'rush';
        });

        $onTime = 0;
        $total = 0;

        foreach ($rushOrders as $order) {
            $orderItems = json_decode($order->items, true) ?? [];
            foreach ($orderItems as $item) {
                if (isset($item['completed_at'])) {
                    $completionTime = strtotime($item['completed_at']);
                    $startTime = strtotime($item['started_at'] ?? $order->created_at);
                    $actualTime = ($completionTime - $startTime) / 60;

                    // Rush orders should be completed within 80% of estimated time
                    if ($actualTime <= ($item['estimated_time'] ?? 15) * 0.8) {
                        $onTime++;
                    }
                    $total++;
                }
            }
        }

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
    }

    /**
     * Calculate peak hour performance
     */
    private function calculatePeakHourPerformance($orders)
    {
        $hourlyData = [];

        foreach ($orders as $order) {
            $hour = $order->created_at->format('H');
            if (!isset($hourlyData[$hour])) {
                $hourlyData[$hour] = [
                    'orders_completed' => 0,
                    'average_time' => 0,
                    'on_time_rate' => 0,
                    'count' => 0
                ];
            }

            $hourlyData[$hour]['orders_completed']++;
            $hourlyData[$hour]['count']++;

            // Calculate average time for this order
            $orderItems = json_decode($order->items, true) ?? [];
            $orderTime = 0;
            $itemCount = 0;

            foreach ($orderItems as $item) {
                if (isset($item['started_at']) && isset($item['completed_at'])) {
                    $startTime = strtotime($item['started_at']);
                    $endTime = strtotime($item['completed_at']);
                    $orderTime += ($endTime - $startTime) / 60;
                    $itemCount++;
                }
            }

            if ($itemCount > 0) {
                $avgOrderTime = $orderTime / $itemCount;
                $hourlyData[$hour]['average_time'] =
                    (($hourlyData[$hour]['average_time'] * ($hourlyData[$hour]['count'] - 1)) + $avgOrderTime) /
                    $hourlyData[$hour]['count'];
            }
        }

        return $hourlyData;
    }

    /**
     * Calculate station performance
     */
    private function calculateStationPerformance($orders, $specificStation = null)
    {
        $stationData = [];

        foreach ($orders as $order) {
            $orderItems = json_decode($order->items, true) ?? [];

            foreach ($orderItems as $item) {
                $station = $item['station'] ?? 'general';

                if ($specificStation && $station !== $specificStation) {
                    continue;
                }

                if (!isset($stationData[$station])) {
                    $stationData[$station] = [
                        'items_completed' => 0,
                        'average_time' => 0,
                        'efficiency_score' => 0,
                        'count' => 0
                    ];
                }

                if (isset($item['completed_at'])) {
                    $stationData[$station]['items_completed']++;

                    if (isset($item['started_at'])) {
                        $startTime = strtotime($item['started_at']);
                        $endTime = strtotime($item['completed_at']);
                        $time = ($endTime - $startTime) / 60;

                        $stationData[$station]['average_time'] =
                            (($stationData[$station]['average_time'] * $stationData[$station]['count']) + $time) /
                            ($stationData[$station]['count'] + 1);
                    }

                    $stationData[$station]['count']++;
                }
            }
        }

        // Calculate efficiency scores
        foreach ($stationData as &$data) {
            if ($data['count'] > 0) {
                // Efficiency based on average time (lower is better)
                $data['efficiency_score'] = max(0, 100 - ($data['average_time'] * 2));
            }
        }

        return $stationData;
    }
}
