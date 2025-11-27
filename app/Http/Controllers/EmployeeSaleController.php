<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Repositories\SalesRepository;
use App\Services\AuthService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Employee Sales",
 *     description="Endpoints for managing employee sales and transactions"
 * )
 * @OA\Security([{"bearerAuth": []}])
 */
class EmployeeSaleController extends BaseController
{
    private $salesrepo;
    private $retail;

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    private function salesRepository()
    {
        $this->retail           = $this->getAccount();
        return $this->salesrepo = new SalesRepository($this->getAccount());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/employee-sales",
     *     tags={"Employee Sales"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all employees with sales or a single employee’s sales",
     *     @OA\Parameter(
     *         name="employee",
     *         in="query",
     *         required=false,
     *         description="Employee ID to filter sales"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful fetch of employees' sales data"
     *     ),
     *     @OA\Response(response=404, description="Employee not found or no sales")
     * )
     */
    public function index($employee = null)
    {
        $this->salesRepository();
        $employees_sales = null;
        if ($employee) {
            $employees_sales = $this->getAccount()->employees()
                ->where('id', $employee)->whereHas('saleTransactions')
                ->with('user')
                ->with('saleTransactions.sales.items')->first();

            if ($employees_sales) {
                $employees_sales['sold_items']      = count($employees_sales->sales()->get());
                $employees_sales['sold_categories'] = count($employees_sales->sales()->distinct('retail_item_id')->get('retail_item_id'));
                $employees_sales['revenue']         = $employees_sales->saleTransactions()->sum('paid_amount');
                return $this->sendResponse($employees_sales, 'Success, Employee sales list');
            } else {
                return $this->sendError("Error", 'Error, Employee has no sales');
            }
        } else {
            $employees_sales = $this->getAccount()->employees()->whereHas('saleTransactions')->with('user')->with('saleTransactions.sales.items')->get();
            return $this->sendResponse($employees_sales, 'Success, employees sales list');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/employee-sales",
     *     tags={"Employee Sales"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new sale record (manual entry)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employee_id", "sales"},
     *             @OA\Property(property="employee_id", type="integer"),
     *             @OA\Property(property="sales", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Sale record created"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        // Implementation for storing new sales (if needed)
    }

    /**
     * @OA\Get(
     *     path="/api/v1/employee-sales/{employee_id}",
     *     tags={"Employee Sales"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get sales data for a specific employee",
     *     @OA\Parameter(
     *         name="employee_id",
     *         in="path",
     *         required=true,
     *         description="Employee ID"
     *     ),
     *     @OA\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         required=false,
     *         description="Optional transaction ID to fetch a single transaction"
     *     ),
     *     @OA\Response(response=200, description="Sales data retrieved successfully"),
     *     @OA\Response(response=404, description="No sales found for employee")
     * )
     */
    public function show($employee_id, $transaction_id = null)
    {
        if ($employee_id && $transaction_id) {
            $employee = $this->getAccount()->employees()
                ->where('id', $employee_id)->whereHas('saleTransactions')
                ->with('user')
                ->with('saleTransactions.sales.items')->first();

            $employee_sales = $employee->saleTransactions()->where('id', $transaction_id)->with('sales.items')->first();

            if ($employee_sales) {
                $employee_sales['employee']        = $employee;
                $employee_sales['sold_items']      = count($employee_sales->sales()->get());
                $employee_sales['sold_categories'] = count($employee_sales->sales()->distinct('retail_item_id')->get('retail_item_id'));
                $employee_sales['revenue']         = $employee_sales->paid_amount;
                return $this->sendResponse($employee_sales, 'Success, Employee sales list');
            } else {
                return $this->sendError("Error", 'Error, Employee has no sales');
            }
        } else {
            $allSales        = $this->salesRepository()->getEmployeeSales($employee_id);
            $solditemscount  = count($allSales['sales']);
            $salesTotalPrice = $allSales['sales']->sum('selling_price');
            $salesdata       = [
                'employee'       => $allSales,
                'solditemscount' => $solditemscount,
                'revenue'        => $salesTotalPrice,
            ];

            return $this->sendResponse($salesdata, 'Success, Employee sales');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/employee-sales/{employee_id}/{transaction_id}",
     *     tags={"Employee Sales"},
     *     security={{"bearerAuth":{}}},
     *     summary="Remove employee from a transaction",
     *     @OA\Parameter(name="employee_id", in="path", required=true, description="Employee ID"),
     *     @OA\Parameter(name="transaction_id", in="path", required=true, description="Transaction ID"),
     *     @OA\Response(response=200, description="Employee removed from transaction"),
     *     @OA\Response(response=400, description="Failed to update transaction")
     * )
     */
    public function destroy($employee_id, $transaction_id = null)
    {
        $employee = $this->getAccount()->employees()
            ->where('id', $employee_id)->first();
        $sale_transaction = $employee->saleTransactions()->where('id', $transaction_id)->first();
        $result           = $sale_transaction->update(
            ['employee_id' => null]
        );

        if ($result) {
            return $this->sendResponse($result, 'Data updated successfully');
        } else {
            return $this->sendError('Error', 'Data could not be updated');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/employee-sales/{employee_id}/items/{sale_item_id}",
     *     tags={"Employee Sales"},
     *     security={{"bearerAuth":{}}},
     *     summary="Remove employee from a sale item",
     *     @OA\Parameter(name="employee_id", in="path", required=true, description="Employee ID"),
     *     @OA\Parameter(name="sale_item_id", in="path", required=true, description="Sale Item ID"),
     *     @OA\Response(response=200, description="Employee removed from sale item"),
     *     @OA\Response(response=400, description="Failed to update sale item")
     * )
     */
    public function destroySaleItem($employee_id, $sale_item_id)
    {
        $employee = $this->getAccount()->employees()
            ->where('id', $employee_id)->first();

        $sale_item = $employee->sales()->where('id', $sale_item_id)->first();
        $result    = $sale_item->update(
            ['employee_id' => null]
        );

        if ($result) {
            return $this->sendResponse($result, 'Data updated successfully');
        } else {
            return $this->sendError('Error', 'Data could not be updated');
        }
    }
}
