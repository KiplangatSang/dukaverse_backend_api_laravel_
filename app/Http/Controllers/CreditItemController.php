<?php
namespace App\Http\Controllers;
use App\Http\Resources\ApiResource;
use App\Services\AuthService;
use App\Services\SaleService;
use Exception;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Credit Items",
 *     description="APIs for managing credit items and fetching credit-related sales data"
 * )
 */
class CreditItemController extends BaseController
{
    private $salesrepo;
    private $retail;


    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource,
        protected readonly SaleService $salesService
    ) {
        parent::__construct($apiResource);
    }



    /**
     * @OA\Get(
     *     path="/api/v1/credit-items",
     *     tags={"Credit Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all credit items with aggregated sales and customer data",
     *     description="Fetches all credit items, counts sold items, and returns related customer credit data",
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched credit items data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="salesdata", type="object",
     *                     @OA\Property(property="allSales", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="soldItems", type="integer", example=15),
     *                     @OA\Property(property="customers", type="array", @OA\Items(type="object"))
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try{
        $creditItems = $this->salesService->getCreditItems();

        $soldItems = 0;
        foreach ($creditItems as $creditItem) {
            $soldItems += count($creditItem->sales);
        }

        $salesdata['allSales']  = $creditItems;
        $salesdata['soldItems'] = $soldItems;
        $salesdata['customers'] = $this->getAccount()->customers()->with('credits')->get();

        return $this->sendResponse(['salesdata' => $salesdata], "Data fetched successfully");

          } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/credit-items",
     *     tags={"Credit Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create new credit item entry",
     *     description="Stores credit items for a given transaction and customer",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id", "customer_id"},
     *             @OA\Property(property="transaction_id", type="string", example="TXN123456"),
     *             @OA\Property(property="customer_id", type="integer", example=45)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Credit item stored successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try{
        $request->validate([
            'transaction_id' => 'required|string',
            'customer_id'    => 'required|integer',
        ]);

        $this->salesService->setCreditItems($request->transaction_id, $request->customer_id);

        return $this->sendResponse([], "Credit item stored successfully", 201);

           } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/credit-items/{id}",
     *     tags={"Credit Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get details of a specific credit item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Credit item ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved credit item"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit item not found"
     *     )
     * )
     */
    public function show($id)
    {
        try{
        // TODO: Implement fetching a single credit item by ID

            } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/credit-items/{id}",
     *     tags={"Credit Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a credit item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Credit item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_id", type="string"),
     *             @OA\Property(property="customer_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit item updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit item not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try{
        // TODO: Implement updating a credit item

           } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/credit-items/{id}",
     *     tags={"Credit Items"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a credit item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Credit item ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Credit item deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Credit item not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        try{
        // TODO: Implement deleting a credit item
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->apiResource->error('Server error.', 500);
            }
    }
}
