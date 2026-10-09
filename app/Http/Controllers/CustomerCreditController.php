<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Events\CustomerInvoice;
use App\Http\Requests\StoreCustomerCreditRequest;
use App\Http\Requests\UpdateCustomerCreditRequest;
use App\Models\CustomerCredit;
use App\Services\AuthService;
use App\Services\CustomerCreditService;
use App\Services\CustomerService;
use App\Services\CustomersService;
use Exception;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Customer Credits",
 *     description="APIs for managing customer credit items, payments, and invoices"
 * )
 */
class CustomerCreditController extends BaseController
{

    public function __construct(
        private readonly AuthService $authService,
        private readonly CustomerService $customerService,
        private readonly CustomerCreditService $customerCreditService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/customer-credits",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get all customer credits",
     *     description="Fetches a list of all customer credits, including related customer and sale transactions.",
     *     @OA\Response(
     *         response=200,
     *         description="Customer credits fetched successfully"
     *     )
     * )
     */
    public function index()
    {
        try {
            $result = $this->customerCreditService->index();
            if (isset($result['error'])) {
                return $this->sendError($result['error'], 'error, credit items could not be fetched', $result['httpCode'] ?? 500);
            }
            $credits = $result['data'];
            return $this->sendResponse($credits, 'success, credit items fetched successfully.');
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->sendError($ex->getMessage(), 'error, credit items could not be fetched.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer-credits/create/{sale_transaction_id}",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Prepare data to create a credit entry",
     *     @OA\Parameter(
     *         name="sale_transaction_id",
     *         in="path",
     *         required=true,
     *         description="Sale transaction ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fetched customers available for credit"
     *     )
     * )
     */
    public function create($sale_transaction_id)
    {
        try{
        $result = $this->customerCreditService->create($sale_transaction_id);
        if (isset($result['error'])) {
            return $this->sendError($result['error'], 'error, could not fetch customers for credit', $result['httpCode'] ?? 500);
        }
        $customerlist = $result['data'];

        if (! $customerlist) {
            return $this->sendError($customerlist, 'Error, could not fetch retail customers');
        }

        return $this->sendResponse($customerlist, 'success, customers available for credit.');

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->sendError($ex->getMessage(), 'error, credit items could not be fetched.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer-credits/{sale_transaction_id}",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new credit item for a transaction",
     *     @OA\Parameter(
     *         name="sale_transaction_id",
     *         in="path",
     *         required=true,
     *         description="Sale transaction ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id"},
     *             @OA\Property(property="customer_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Credit item created successfully"
     *     )
     * )
     */
    public function store($sale_transaction_id, StoreCustomerCreditRequest $request)
    {

        try {
            $result = $this->customerCreditService->store($sale_transaction_id, $request->all());

            if (isset($result['error'])) {
                return $this->sendError($result['error'], 'error, could not set this credit item', $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Success, credit item set successfully.');
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->sendError($ex->getMessage(), 'Error, could not set this credit item.');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer-credits/{id}",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get details for a single credit item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer credit ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Credit item fetched successfully"),
     *     @OA\Response(response=404, description="Credit item not found")
     * )
     */
    public function show(CustomerCredit $customerCredit)
    {
       try{
         $result = $this->customerCreditService->show($customerCredit->id);
        if (isset($result['error'])) {
            return $this->sendError($result['error'], 'error, credit item could not be fetched', $result['httpCode'] ?? 500);
        }
        $credit = $result['data'];

        return $this->sendResponse($credit, 'success, credit item fetched successfully');

        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->sendError($ex->getMessage(), 'error, credit items could not be fetched.');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/customer-credits/{id}",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a customer credit item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer credit ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount_paid", type="number", example=1500.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Credit item updated successfully")
     * )
     */
    public function update(UpdateCustomerCreditRequest $request, $customerCredit)
    {
        try {

            $result = $this->customerCreditService->update($request->all(), $customerCredit);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], 'Error updating credit item.', $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Success, credit item updated successfully.');
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), 'Error updating credit item.');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customer-credits/{id}",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a customer credit",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer credit ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Credit item deleted successfully")
     * )
     */
    public function destroy(CustomerCredit $customerCredit)
    {
        try {
            $result = $this->customerCreditService->destroy($customerCredit);
            if (isset($result['error'])) {
                return $this->sendError($result['error'], 'Error deleting credit item.', $result['httpCode'] ?? 500);
            }
            return $this->sendResponse([], 'Success, credit item deleted successfully.');
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), 'Error deleting credit item.');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customer-credits/invoice",
     *     tags={"Customer Credits"},
     *     security={{"bearerAuth":{}}},
     *     summary="Send an invoice to a customer",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"customer_id","credit_id"},
     *             @OA\Property(property="customer_id", type="integer", example=1),
     *             @OA\Property(property="credit_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Invoice sent successfully")
     * )
     */
    public function invoice(Request $request)
    {
        try {

            $result = $this->customerCreditService->invoice($request->all());
            if (isset($result['error'])) {
                return $this->sendError($result['error'], 'error, could not send the
                    customer an invoice', $result['httpCode'] ?? 500);
            }
            return $this->sendResponse($result['data'], 'Success, customer invoice sent successfully.');
        } catch (Exception $e) {
            info($e->getMessage());
            return $this->sendError($e->getMessage(), 'error, could not send this invoice.');
        }
    }
}
