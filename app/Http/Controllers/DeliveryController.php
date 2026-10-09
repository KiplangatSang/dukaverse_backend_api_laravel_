<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Exception;

class DeliveryController extends BaseController
{
    public function __construct(
        private readonly DeliveryService $deliveryService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery/orders",
     *     summary="Get delivery orders",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="rider_id",
     *         in="query",
     *         description="Filter by rider ID",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery orders retrieved successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['restaurant_id', 'rider_id', 'status', 'date_from', 'date_to', 'limit']);
            $result = $this->deliveryService->getDeliveryOrders($filters);

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery/orders",
     *     summary="Create delivery order",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","delivery_location"},
     *             @OA\Property(property="order_id", type="string", format="uuid"),
     *             @OA\Property(property="pickup_location", type="object"),
     *             @OA\Property(property="delivery_location", type="object"),
     *             @OA\Property(property="estimated_distance", type="number"),
     *             @OA\Property(property="estimated_duration", type="integer"),
     *             @OA\Property(property="fee", type="number"),
     *             @OA\Property(property="special_instructions", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Delivery order created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $result = $this->deliveryService->createDeliveryOrder($request->validated());

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/delivery/orders/{orderId}/status",
     *     summary="Update delivery order status",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Delivery Order ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"assigned","picked_up","in_transit","delivered","cancelled","failed"}),
     *             @OA\Property(property="rider_id", type="string", format="uuid"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery status updated successfully"
     *     )
     * )
     */
    public function updateStatus(Request $request, $orderId)
    {
        try {
            $result = $this->deliveryService->updateDeliveryStatus($orderId, $request->validated());

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/delivery/orders/{orderId}/assign",
     *     summary="Assign rider to delivery",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Delivery Order ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rider_id"},
     *             @OA\Property(property="rider_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rider assigned successfully"
     *     )
     * )
     */
    public function assignRider(Request $request, $orderId)
    {
        try {
            $result = $this->deliveryService->assignRider($orderId, $request->rider_id);

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery/riders/available",
     *     summary="Get available riders",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Location coordinates (lat,lng)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Search radius in km",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available riders retrieved successfully"
     *     )
     * )
     */
    public function getAvailableRiders(Request $request)
    {
        try {
            $filters = $request->only(['location', 'radius', 'vehicle_type']);
            $result = $this->deliveryService->getAvailableRiders($filters);

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery/riders/{riderId}/location",
     *     summary="Update rider location",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="riderId",
     *         in="path",
     *         required=true,
     *         description="Rider ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="accuracy", type="number"),
     *             @OA\Property(property="speed", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rider location updated successfully"
     *     )
     * )
     */
    public function updateRiderLocation(Request $request, $riderId)
    {
        try {
            $result = $this->deliveryService->updateRiderLocation($riderId, $request->all());

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery/orders/{orderId}/tracking",
     *     summary="Get delivery tracking information",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Delivery Order ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery tracking information retrieved successfully"
     *     )
     * )
     */
    public function getTracking(Request $request, $orderId)
    {
        try {
            $result = $this->deliveryService->getDeliveryTracking($orderId);

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/delivery/orders/{orderId}/rating",
     *     summary="Submit delivery rating",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Delivery Order ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="rider_rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="food_rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="packaging_rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string"),
     *             @OA\Property(property="tip_amount", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery rating submitted successfully"
     *     )
     * )
     */
    public function submitRating(Request $request, $orderId)
    {
        try {
            $result = $this->deliveryService->submitDeliveryRating($orderId, $request->all());

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/delivery/reports/performance",
     *     summary="Get delivery performance report",
     *     tags={"Delivery"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="rider_id",
     *         in="query",
     *         description="Filter by rider ID",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery performance report generated successfully"
     *     )
     * )
     */
    public function getPerformanceReport(Request $request)
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'rider_id', 'restaurant_id']);
            $result = $this->deliveryService->getDeliveryPerformanceReport($filters);

            if (isset($result["error"])) {
                return $this->apiResource->error("Error occurred", $result["httpCode"], $result["error"]);
            }

            return $this->apiResource->success($result["data"], $result["message"], $result["httpCode"]);
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error("Server Error", 500, [$ex->getMessage()]);
        }
    }
}
