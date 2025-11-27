<?php
namespace App\Http\Controllers;

use App\Events\CustomerInvoice;
use App\Http\Requests\StoreCustomerCreditRequest;
use App\Http\Requests\UpdateCustomerCreditRequest;
use App\Models\CustomerCredit;
use App\Repositories\CustomersRepository;
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
    public function customersRepository()
    {
        $ordersRepo = new CustomersRepository($this->getAccount());
        return $ordersRepo;
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
        $credits = $this->getAccount()->customerCredits()
            ->with('customer')
            ->with('saleTransaction')
            ->orderBy('created_at', "DESC")
            ->get();

        if (! $credits) {
            return $this->sendError($credits, 'error, credit items could not be fetched');
        }

        return $this->sendResponse($credits, 'success, credit items fetched successfully.');
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
        $customerlist['customers'] = $this->getAccount()->customers()->latest()->get();
        if (! $customerlist) {
            return $this->sendError($customerlist, 'Error, could not fetch retail customers');
        }

        $customerlist['transaction'] = $this->getAccount()->saleTransactions()->where("transaction_id", $sale_transaction_id)->first();
        return $this->sendResponse($customerlist, 'success, customers available for credit.');
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
        $customer = $this->getAccount()->customers()->where('id', $request->customer_id)->first();

        try {
            $transaction = $this->getAccount()->saleTransactions()
                ->where('transaction_id', $sale_transaction_id)->first();

            $transaction->update(['on_credit' => true]);

            $this->getAccount()->sales()
                ->where('sale_transaction_id', $transaction->id)
                ->update(['on_credit' => true]);

            $credit_item = $this->getAccount()->customerCredits()->create([
                'sale_transaction_id' => $transaction->id,
                "amount"              => $transaction->transaction_amount,
                "amount_paid"         => $transaction->paid_amount,
                "pay_status"          => $transaction->transaction_amount == $transaction->paid_amount,
            ]);

            $credit_item->customer()->save($customer);
            $transaction->customer()->associate($customer);
            $transaction->save();

            return $this->sendResponse($credit_item->refresh(), 'Credit item created successfully.');
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
        $credit = $this->customersRepository()->getCustomersCredit($customerCredit->id);

        if (! $credit) {
            return $this->sendError($credit, 'error, could not fetch this credit item');
        }

        return $this->sendResponse($credit, 'success, credit item fetched successfully');
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
    public function update(UpdateCustomerCreditRequest $request, CustomerCredit $customerCredit)
    {
        try {
            $customercredit = $this->getAccount()
                ->customerCredits()
                ->where('id', $customerCredit->id)
                ->first();

            $pay_status            = $request->amount_paid == $customercredit->amount;
            $request['pay_status'] = $pay_status;

            $result = $customercredit->update($request->except('_token'));
            return $result;
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
        $customerCredit->customer()->detach($customerCredit->customer);
        CustomerCredit::destroy($customerCredit->id);
        return $this->sendResponse([], 'Success, credit item deleted.');
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
            $customer = $this->getAccount()->customers()->where('id', $request->customer_id)->first();
            $credit   = $this->getAccount()->customerCredits()->where('id', $request->credit_id)->first();
            $result   = CustomerInvoice::dispatch($customer, $credit);

            if (! $result) {
                return $this->sendError($result, 'error, could not send the customer an invoice');
            }

            return $this->sendResponse($result, 'Success, customer invoice sent successfully.');
        } catch (Exception $e) {
            info($e->getMessage());
            return $this->sendError($e->getMessage(), 'error, could not send this invoice.');
        }
    }
}
