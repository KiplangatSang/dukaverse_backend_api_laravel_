<?php
namespace App\Http\Controllers;

use App\Repositories\OrdersRepository;

/**
 * @OA\Tag(
 *     name="Order Pending",
 *     description="APIs for retrieving and managing pending orders"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class OrderPendingController extends BaseController
{
    private $ordersRepo;
    private $retail;

    /**
     * Repository accessor
     */
    public function ordersRepository()
    {
        $this->ordersRepo = new OrdersRepository($this->user());
        return $this->ordersRepo;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/pending",
     *     tags={"Order Pending"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all pending orders",
     *     description="Retrieve a list of all pending orders for the authenticated user",
     *     operationId="getPendingOrders",
     *     @OA\Response(
     *         response=200,
     *         description="List of pending orders",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $this->ordersRepository();
        $ordersdata = $this->ordersRepository()->getPendingOrders();
        return response()->json($ordersdata, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/pending/{id}",
     *     tags={"Order Pending"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get details of a pending order",
     *     description="Retrieve the details of a specific pending order by its ID",
     *     operationId="showPendingOrder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pending order to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending order details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="orders", type="object"),
     *             @OA\Property(property="ordersitems", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pending order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(string $id)
    {
        $this->ordersRepository();
        $orders = $this->user()->orders()->where('id', $id)->first();

        if (! $orders) {
            return response()->json(['error' => 'Pending order not found'], 404);
        }

        $orders->ordered_items = json_decode($orders->ordered_items);
        $ordersitems           = count((array) $orders->ordered_items);

        $ordersdata = [
            'orders'      => $orders,
            'ordersitems' => $ordersitems,
        ];

        return response()->json($ordersdata, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/pending/{id}",
     *     tags={"Order Pending"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a pending order",
     *     description="Delete a specific pending order by its ID",
     *     operationId="deletePendingOrder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pending order to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending order deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pending order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $deleted = $this->user()->orders()->delete($id);

        if (! $deleted) {
            return response()->json(['message' => 'Pending order not found'], 404);
        }

        return response()->json(['message' => 'success'], 200);
    }
}
