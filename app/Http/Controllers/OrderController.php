<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\ApiResource;
use App\Models\Order;
use App\Repositories\OrdersRepository;
use App\Services\AuthService;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for managing orders"
 * )
 */
class OrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
        $this->orderService = new OrderService(new OrdersRepository($this->getAccount()), $this->getAccount());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     tags={"Orders"},
     *     summary="Get all orders",
     *     description="Fetches all orders for the authenticated retail user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        try {
            $ordersdata = $this->orderService->getIndexData();
            return $this->sendResponse($ordersdata, "Success");
        } catch (Exception $e) {
            return $this->sendError('Error retrieving orders', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Create a new order with the selected items and calculates projected cost.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 required={"id","required_amount"},
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="required_amount", type="number", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request);
            return $this->sendResponse($order, 'success, order sent successfully');
        } catch (Exception $e) {
            return $this->sendError('Error creating order', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get a specific order",
     *     description="Retrieve detailed information about a specific order by ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show(Order $order)
    {
        try {
            $ordersdata = $this->orderService->getShowData($order->id);
            return $this->sendResponse($ordersdata, 'success, order sent successfully');
        } catch (Exception $e) {
            return $this->sendError('Error retrieving order', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}/edit",
     *     tags={"Orders"},
     *     summary="Edit order (form data)",
     *     description="Placeholder for order edit form data.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(response=200, description="Returns order edit data")
     * )
     */
    public function edit(Order $order)
    {
        // Not implemented - placeholder for edit form
    }

    /**
     * @OA\Put(
     *     path="/api/v1/orders/{id}",
     *     tags={"Orders"},
     *     summary="Update an order",
     *     description="Update an existing order with new data. (Currently not implemented)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order_status", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order updated successfully"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        // Not implemented yet
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/{id}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     description="Delete an existing order by ID. (Currently not implemented)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
         *         in="path",
         *         description="Order ID",
         *         required=true,
         *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(response=204, description="Order deleted successfully"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function destroy(Order $order)
    {
        // Not implemented yet
    }
}
