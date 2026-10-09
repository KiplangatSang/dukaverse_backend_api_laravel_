<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Exception;

class MenuController extends BaseController
{
    public function __construct(
        private readonly MenuService $menuService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurantId}/menu/categories",
     *     summary="Get menu categories",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu categories retrieved successfully"
     *     )
     * )
     */
    public function getCategories(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['status', 'parent_id']);
            $result = $this->menuService->getCategories($restaurantId, $filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/categories",
     *     summary="Create menu category",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="image_url", type="string"),
     *             @OA\Property(property="parent_id", type="string", format="uuid"),
     *             @OA\Property(property="sort_order", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu category created successfully"
     *     )
     * )
     */
    public function createCategory(Request $request, $restaurantId)
    {
        try {
            $result = $this->menuService->createCategory($restaurantId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/categories/{categoryId}",
     *     summary="Update menu category",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="Category ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu category updated successfully"
     *     )
     * )
     */
    public function updateCategory(Request $request, $restaurantId, $categoryId)
    {
        try {
            $result = $this->menuService->updateCategory($restaurantId, $categoryId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/items",
     *     summary="Get menu items",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu items retrieved successfully"
     *     )
     * )
     */
    public function getItems(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['category_id', 'status', 'is_available', 'search', 'tags', 'limit']);
            $result = $this->menuService->getItems($restaurantId, $filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/items",
     *     summary="Create menu item",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu item created successfully"
     *     )
     * )
     */
    public function createItem(Request $request, $restaurantId)
    {
        try {
            $result = $this->menuService->createItem($restaurantId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/items/{itemId}",
     *     summary="Update menu item",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="Menu Item ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu item updated successfully"
     *     )
     * )
     */
    public function updateItem(Request $request, $restaurantId, $itemId)
    {
        try {
            $result = $this->menuService->updateItem($restaurantId, $itemId, $request->all());

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
     *     path="/api/v1/restaurants/{restaurantId}/menu",
     *     summary="Get complete menu",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Complete menu retrieved successfully"
     *     )
     * )
     */
    public function getCompleteMenu(Request $request, $restaurantId)
    {
        try {
            $result = $this->menuService->getCompleteMenu($restaurantId);

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/search",
     *     summary="Search menu items",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu search completed successfully"
     *     )
     * )
     */
    public function searchItems(Request $request, $restaurantId)
    {
        try {
            $query = $request->get('q');
            $filters = $request->only(['category_id', 'max_price', 'dietary_restrictions', 'limit']);

            if (!$query) {
                return $this->apiResource->error("Search query is required", 400);
            }

            $result = $this->menuService->searchItems($restaurantId, $query, $filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/items/{itemId}/availability",
     *     summary="Update item availability",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         required=true,
     *         description="Menu Item ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_available"},
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item availability updated successfully"
     *     )
     * )
     */
    public function updateItemAvailability(Request $request, $restaurantId, $itemId)
    {
        try {
            $result = $this->menuService->updateItemAvailability($restaurantId, $itemId, $request->is_available);

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
     *     path="/api/v1/restaurants/{restaurantId}/menu/bulk-availability",
     *     summary="Bulk update item availability",
     *     tags={"Menu"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *                 required={"id","is_available"},
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="is_available", type="boolean")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk availability update completed successfully"
     *     )
     * )
     */
    public function bulkUpdateAvailability(Request $request, $restaurantId)
    {
        try {
            $result = $this->menuService->bulkUpdateAvailability($restaurantId, $request->validated());

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
