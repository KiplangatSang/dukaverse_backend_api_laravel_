<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Services\RestaurantService;
use Illuminate\Http\Request;
use Exception;

class RestaurantController extends BaseController
{
    public function __construct(
        private readonly RestaurantService $restaurantService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants",
     *     summary="Get restaurants",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Filter by owner ID",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or description",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurants retrieved successfully"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['owner_id', 'status', 'search', 'limit']);
            $result = $this->restaurantService->getRestaurants($filters);

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
     *     path="/api/v1/restaurants",
     *     summary="Create restaurant",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","address","phone"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="website", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="operating_hours", type="object"),
     *             @OA\Property(property="tax_rate", type="number"),
     *             @OA\Property(property="currency", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Restaurant created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $result = $this->restaurantService->createRestaurant($request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}",
     *     summary="Get restaurant details",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant details retrieved successfully"
     *     )
     * )
     */
    public function show(Request $request, $restaurantId)
    {
        try {
            $result = $this->restaurantService->getRestaurants(['id' => $restaurantId]);

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
     *     path="/api/v1/restaurants/{restaurantId}",
     *     summary="Update restaurant",
     *     tags={"Restaurants"},
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
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","suspended"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $restaurantId)
    {
        try {
            $result = $this->restaurantService->updateRestaurant($restaurantId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/tables",
     *     summary="Get restaurant tables",
     *     tags={"Restaurants"},
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
     *         description="Filter by status",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tables retrieved successfully"
     *     )
     * )
     */
    public function getTables(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['status', 'capacity', 'section']);
            $result = $this->restaurantService->getTables($restaurantId, $filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/tables",
     *     summary="Create restaurant table",
     *     tags={"Restaurants"},
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
     *             required={"table_number","capacity"},
     *             @OA\Property(property="table_number", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="section", type="string"),
     *             @OA\Property(property="features", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Table created successfully"
     *     )
     * )
     */
    public function createTable(Request $request, $restaurantId)
    {
        try {
            $result = $this->restaurantService->createTable($restaurantId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/tables/{tableId}/status",
     *     summary="Update table status",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="tableId",
     *         in="path",
     *         required=true,
     *         description="Table ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"available","occupied","reserved","cleaning"}),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Table status updated successfully"
     *     )
     * )
     */
    public function updateTableStatus(Request $request, $restaurantId, $tableId)
    {
        try {
            $result = $this->restaurantService->updateTableStatus(
                $restaurantId,
                $tableId,
                $request->status,
                $request->notes
            );

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
     *     path="/api/v1/restaurants/{restaurantId}/staff",
     *     summary="Get restaurant staff",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role",
     *         required=false
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Staff retrieved successfully"
     *     )
     * )
     */
    public function getStaff(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['role', 'status', 'search', 'limit']);
            $result = $this->restaurantService->getStaff($restaurantId, $filters);

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
     *     path="/api/v1/restaurants/{restaurantId}/staff",
     *     summary="Add staff to restaurant",
     *     tags={"Restaurants"},
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
     *             required={"user_id","first_name","last_name","email","role","role_id"},
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="role_id", type="string", format="uuid"),
     *             @OA\Property(property="hourly_rate", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Staff member added successfully"
     *     )
     * )
     */
    public function addStaff(Request $request, $restaurantId)
    {
        try {
            $result = $this->restaurantService->addStaff($restaurantId, $request->validated());

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
     *     path="/api/v1/restaurants/{restaurantId}/analytics",
     *     summary="Get restaurant analytics",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurantId",
     *         in="path",
     *         required=true,
     *         description="Restaurant ID"
     *     ),
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
     *     @OA\Response(
     *         response=200,
     *         description="Analytics retrieved successfully"
     *     )
     * )
     */
    public function getAnalytics(Request $request, $restaurantId)
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'period']);
            $result = $this->restaurantService->getAnalytics($restaurantId, $filters);

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
