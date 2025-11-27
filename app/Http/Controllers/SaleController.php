<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\Sale;
use App\Repositories\SalesRepository;
use App\Services\AuthService;
use App\Services\SaleService;
use Exception;
use Illuminate\Http\Request;

class SaleController extends BaseController
{
    private SaleService $saleService;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
        $this->saleService = new SaleService(new SalesRepository($this->getAccount()));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales",
     *     summary="Get all sales",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of sales retrieved successfully"
     *     )
     * )
     * @OA\Get(
     *     path="/api/v1/sales/{retail_item}",
     *     summary="Get sales for a specific retail item",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="retail_item",
     *         in="path",
     *         required=true,
     *         description="Retail Item ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sales data for the specified retail item"
     *     )
     * )
     */
    public function index($retail_item = null)
    {
        try {
            if (! $retail_item) {
                $salesdata = $this->saleService->getIndexData();
                return $this->sendResponse($salesdata, 'success');
            } else {
                $salesdata = $this->saleService->getShowData($retail_item);
                return $this->sendResponse($salesdata, 'success, sales data retrieved successfully.');
            }
        } catch (Exception $e) {
            return $this->sendError('Error retrieving sales data', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/create",
     *     summary="Get data needed to create a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sale creation data retrieved successfully"
     *     )
     * )
     */
    public function create()
    {
        try {
            $salesdata = $this->saleService->getCreateData();
            return $this->sendResponse($salesdata, 'success,Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Error retrieving create data', $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sales",
     *     summary="Store a new sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sale data to be stored"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale stored successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error saving sale"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $result = $this->saleService->createSale($request->validated());

            $revenue_result = $this->saveRevenue($request->price);
            if (! $revenue_result) {
                throw new Exception('Error saving revenue from item');
            }

            return $this->sendResponse($result, 'success,Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Error saving sale', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/{id}",
     *     summary="Get a specific sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale data retrieved successfully"
     *     )
     * )
     */
    public function show(Sale $sales)
    {
        try {
            $salesdata = $this->saleService->getShowData($sales->id);
            return $this->sendResponse($salesdata, 'success,Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Error retrieving sale data', $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/{id}/edit",
     *     summary="Get data for editing a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale data for editing retrieved successfully"
     *     )
     * )
     */
    public function edit(Sale $sales)
    {
        try {
            $salesdata = $this->saleService->getShowData($sales->id);
            return $this->sendResponse($salesdata, 'success,Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Error retrieving edit data', $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/sales/{id}",
     *     summary="Update a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Sale data to update"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale updated successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating sale"
     *     )
     * )
     */
    public function update(Request $request, Sale $sales)
    {
        try {
            $result = $this->saleService->updateSale($sales, $request->validated());

            $revenue_result = $this->saveRevenue($request->price);
            if (! $revenue_result) {
                throw new Exception('Error saving revenue from item');
            }

            return $this->sendResponse($result, 'success,Data retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Error updating sale', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/sales/{id}",
     *     summary="Delete a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sale ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sale deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Could not delete sale"
     *     )
     * )
     */
    public function destroy(Sale $sales)
    {
        try {
            $this->saleService->deleteSale($sales->id);
            return back()->with('success', 'item deleted successfully');
        } catch (Exception $e) {
            return back()->with('error', 'Could not delete this item: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sales/days",
     *     summary="Get daily sales data",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daily sales data retrieved successfully"
     *     )
     * )
     */
    public function getDaysSales()
    {
        try {
            $sales = $this->saleService->getDailySales();
            return $this->sendResponse($sales, 'success');
        } catch (Exception $e) {
            return $this->sendError('Error retrieving daily sales', $e->getMessage());
        }
    }
}
