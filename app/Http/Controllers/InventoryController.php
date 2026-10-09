<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Inventory",
 *     description="Inventory management endpoints"
 * )
 */
class InventoryController extends BaseController
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory",
     *     summary="Get inventory data",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Inventory data retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving inventory data"
     *     )
     * )
     */
    public function index()
    {
        try {
            $result = $this->inventoryService->index();
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
     *     path="/api/v1/inventory/convert-required-to-order",
     *     summary="Convert required item to order",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"required_item_id", "supplier_id"},
     *             @OA\Property(property="required_item_id", type="integer", example=1),
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order created successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating order"
     *     )
     * )
     */
    public function convertRequiredToOrder(Request $request)
    {
        try {
            $result = $this->inventoryService->convertRequiredItemToOrder($request->required_item_id, $request->all());
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
     *     path="/api/v1/inventory/convert-order-to-supply",
     *     summary="Convert order to supply",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supply created successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating supply"
     *     )
     * )
     */
    public function convertOrderToSupply(Request $request)
    {
        try {
            $result = $this->inventoryService->convertOrderToSupply($request->order_id, $request->all());
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
     *     path="/api/v1/inventory/convert-supply-to-stock",
     *     summary="Convert supply to stock",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supply_id"},
     *             @OA\Property(property="supply_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock created successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating stock"
     *     )
     * )
     */
    public function convertSupplyToStock(Request $request)
    {
        try {
            $result = $this->inventoryService->convertSupplyToStock($request->supply_id, $request->all());
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
     *     path="/api/v1/inventory/trace-supply/{supply_id}",
     *     summary="Trace supply back to order",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="supply_id",
     *         in="path",
     *         required=true,
     *         description="Supply ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order traced successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function traceSupplyToOrder($supplyId)
    {
        try {
            $result = $this->inventoryService->traceSupplyToOrder($supplyId);
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
     *     path="/api/v1/inventory/restock-stock",
     *     summary="Restock stock from supply",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stock_id", "supply_id", "quantity"},
     *             @OA\Property(property="stock_id", type="integer", example=1),
     *             @OA\Property(property="supply_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock restocked successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Insufficient quantity in supply"
     *     )
     * )
     */
    public function restockStockFromSupply(Request $request)
    {
        try {
            $result = $this->inventoryService->restockStockFromSupply($request->stock_id, $request->supply_id, $request->quantity);
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
