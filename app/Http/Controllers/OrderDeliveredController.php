<?php
namespace App\Http\Controllers;

use App\Repositories\OrdersRepository;

/**
 * @OA\Tag(
 *     name="Order Delivered",
 *     description="APIs for retrieving and managing delivered orders"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class OrderDeliveredController extends BaseController
{
    private $ordersRepo;

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
     *     path="/api/v1/orders/delivered",
     *     tags={"Order Delivered"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all delivered orders",
     *     description="Retrieve a list of all delivered orders for the authenticated user",
     *     operationId="getDeliveredOrders",
     *     @OA\Response(
     *         response=200,
     *         description="List of delivered orders",
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
        $ordersdata = $this->ordersRepository()->getDeliveredOrders();
        return response()->json($ordersdata, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/delivered/{id}",
     *     tags={"Order Delivered"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get details of a delivered order",
     *     description="Retrieve the details of a specific delivered order by its ID",
     *     operationId="showDeliveredOrder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivered order details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="orders", type="object"),
     *             @OA\Property(property="ordersitems", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(string $id)
    {
        $orders = $this->user()->orders()->where('id', $id)->first();

        if (! $orders) {
            return response()->json(['error' => 'Order not found'], 404);
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
     *     path="/api/v1/orders/delivered/{id}",
     *     tags={"Order Delivered"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a delivered order",
     *     description="Delete a specific delivered order by its ID (requires authorization)",
     *     operationId="deleteDeliveredOrder",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not authorized to delete"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // $this->authorize('delete');

        $result = $this->user()->orders()->destroy($id);

        if (! $result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json(['message' => 'success'], 200);
    }
}
