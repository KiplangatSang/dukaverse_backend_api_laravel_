<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\KitchenService;
use Illuminate\Http\Request;
use Exception;

class KitchenController extends BaseController
{
    public function __construct(
        private readonly KitchenService $kitchenService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurantId}/kitchen/orders",
     *     summary="Get kitchen orders",
     *     tags={"Kitchen"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (confirmed, preparing, ready)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="station",
     *         in="query",
     *         description="Filter by station (grill, salad, beverage, etc.)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority (normal, urgent, rush)",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kitchen orders retrieved successfully"
     *     )
     * )
     */
    public function getOrders(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['status', 'station', 'priority', 'limit']);
            $result = $this->kitchenService->getKitchenOrders($filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/kitchen/orders/{orderId}/items/{itemId}/status",
     *     summary="Update kitchen item status",
     *     tags={"Kitchen"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="Order ID"
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="Order Item ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","preparing","ready","cancelled"}),
     *             @OA\Property(property="station", type="string"),
     *             @OA\Property(property="assigned_to", type="string"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item status updated successfully"
     *     )
     * )
     */
    public function updateItemStatus(Request $request, $restaurantId, $orderId, $itemId)
    {
        try {
            $result = $this->kitchenService->updateItemStatus($orderId, $itemId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/kitchen/stations",
     *     summary="Get kitchen stations",
     *     tags={"Kitchen"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kitchen stations retrieved successfully"
     *     )
     * )
     */
    public function getStations(Request $request, $restaurantId)
    {
        try {
            $result = $this->kitchenService->getKitchenStations();

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
     *     path="/api/v1/restaurants/{restaurantId}/kitchen/performance",
     *     summary="Get kitchen performance metrics",
     *     tags={"Kitchen"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period (today, week, month)",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="station",
     *         in="query",
     *         description="Filter by specific station",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kitchen performance metrics retrieved successfully"
     *     )
     * )
     */
    public function getPerformance(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['period', 'station']);
            $result = $this->kitchenService->getKitchenPerformance($filters);

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
