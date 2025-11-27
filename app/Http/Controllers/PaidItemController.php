<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\Sale;
use App\Repositories\SalesRepository;
use App\Services\AuthService;

/**
 * @OA\Tag(
 *     name="Paid Items",
 *     description="APIs for managing and viewing paid sold items"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class PaidItemController extends BaseController
{
    private $salesrepo;
    private $retail;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Repository accessor for sales.
     */
    public function salesRepository()
    {
        $this->retail = $this->getAccount();
        if (! $this->retail) {
            return false;
        }

        return new SalesRepository($this->retail);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/paid-items",
     *     tags={"Paid Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all paid sold items",
     *     description="Retrieve a list of all items that have been sold and fully paid for.",
     *     operationId="getPaidSoldItems",
     *     @OA\Response(
     *         response=200,
     *         description="List of paid sold items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="item", type="string", example="Laptop"),
     *                 @OA\Property(property="amount", type="number", format="float", example=3),
     *                 @OA\Property(property="price", type="number", format="float", example=45000),
     *                 @OA\Property(property="total", type="number", format="float", example=135000),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", example="2025-09-13T12:30:00Z")
     *             )
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
        $paidItems             = $this->salesRepository()->getPaidSoldItems();
        $salesdata['allSales'] = $paidItems;
        return view('client.sales.paiditems.index', compact('salesdata'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/paid-items/{id}",
     *     tags={"Paid Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get paid sold items by ID",
     *     description="Retrieve details of paid sold items for a specific sale ID.",
     *     operationId="getPaidSoldItemsById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paid sold items for given sale ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="allSales", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sale not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $allSales              = $this->salesRepository()->getPaidSoldItems();
        $salesdata['allSales'] = $allSales;

        return view('client.sales.paiditems.show', compact('salesdata'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/sales/paid-items/{id}",
     *     tags={"Paid Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a paid sold item",
     *     description="Deletes a specific paid sold item by its ID.",
     *     operationId="deletePaidItem",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID of the item to delete",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Deletion Successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Deletion failed"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function delete($sale_id)
    {
        $result = Sale::destroy($sale_id);
        if (! $result) {
            return back()->with('error', 'Could not delete item');
        }

        return back()->with('success', 'Deletion Successful');
    }
}
