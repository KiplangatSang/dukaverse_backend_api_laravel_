<?php

namespace App\Services;

use App\Events\CustomerInvoice;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;
use App\Models\CustomerCredit;
use Illuminate\Support\Facades\Validator;

class CustomerCreditService extends BaseService
{


    public function __construct(
        StoreFileResource $storeFileResource,
        private readonly ResponseHelper $responseHelper,
        private readonly CustomerService $customersService
    ) {
        parent::__construct($storeFileResource, $responseHelper);
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
        $credits = $this->account->customerCredits()
            ->with('customer')
            ->with('saleTransaction')
            ->orderBy('created_at', "DESC")
            ->get();

        if (! $credits) {
            return $this->responseHelper->error($credits, 'error, credit items could not be fetched');
        }

        return $this->responseHelper->respond($credits->toArray(), 'success, credit items fetched successfully.');
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
        $customerlist['customers'] = $this->account->customers()->latest()->get();
        if (! $customerlist) {
            return $this->responseHelper->error($customerlist, 'Error, could not fetch retail customers');
        }

        $customerlist['transaction'] = $this->account->saleTransactions()->where("transaction_id", $sale_transaction_id)->first();
        return $this->responseHelper->respond($customerlist, 'success, customers available for credit.');
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
    public function store($sale_transaction_id, array $data)
    {

        $validated = Validator::make($data, [
            'customer_id' => 'required|integer|exists:customers,id',
        ]);
        if ($validated->fails()) {
            return $this->responseHelper->error('Validation Error', $validated->errors());
        }
        $customer = $this->account->customers()->where('id', $data['customer_id'])->first();

        try {
            $transaction = $this->account->saleTransactions()
                ->where('transaction_id', $sale_transaction_id)->first();

            $transaction->update(['on_credit' => true]);

            $this->account->sales()
                ->where('sale_transaction_id', $transaction->id)
                ->update(['on_credit' => true]);

            $credit_item = $this->account->customerCredits()->create([
                'sale_transaction_id' => $transaction->id,
                "amount"              => $transaction->transaction_amount,
                "amount_paid"         => $transaction->paid_amount,
                "pay_status"          => $transaction->transaction_amount == $transaction->paid_amount,
            ]);

            $credit_item->customer()->save($customer);
            $transaction->customer()->associate($customer);
            $transaction->save();

            return $this->responseHelper->respond($credit_item->refresh(), 'Credit item created successfully.');
        } catch (Exception $ex) {
            info($ex->getMessage());
            return $this->responseHelper->error($ex->getMessage(), 'Error, could not set this credit item.');
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
    public function show(string $customerCredit)
    {
        $credit = $this->customersService->getCustomersCredit($customerCredit);

        if (! $credit) {
            return $this->responseHelper->error($credit, 'error, could not fetch this credit item');
        }

        return $this->responseHelper->respond($credit, 'success, credit item fetched successfully');
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
    public function update($data, string $customerCredit)
    {
        try {
            $validated = Validator::make($data, [
                'amount_paid' => 'required|numeric|min:0',
            ]);
            if ($validated->fails()) {
                return $this->responseHelper->error('Validation Error', $validated->errors());
            }

            $customercredit = $this->account
                ->customerCredits()
                ->where('id', $customerCredit)
                ->first();

            $pay_status            = $data['amount_paid'] == $customercredit->amount;
            $request['pay_status'] = $pay_status;

            $result = $customercredit->update($data);
            return $this->responseHelper->respond($customercredit->refresh(), 'Success, credit item updated.');
        } catch (Exception $ex) {
            return $this->responseHelper->error($ex->getMessage(), 'Error updating credit item.');
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
    public function destroy(string $customerCredit)
    {
        $customerCredit = $this->account
            ->customerCredits()
            ->where('id', $customerCredit)
            ->first();
        if (! $customerCredit) {
            return $this->responseHelper->error([], 'Error, credit item could not be found.');
        }
        $customerCredit->customer()->detach($customerCredit->customer);
        if (! $customerCredit) {
            return $this->responseHelper->error([], 'Error, credit item could not be found.');
        }
        $result = CustomerCredit::destroy($customerCredit->id);
        if (! $result) {
            return $this->responseHelper->error('Bad request', ['message' => 'Credit item could not be deleted']);
        }
        return $this->responseHelper->respond([], 'Success, credit item deleted.');
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
    public function invoice($data)
    {
        try {
            $validated = Validator::make($data, [
                'customer_id' => 'required|integer|exists:customers,id',
                'credit_id'   => 'required|integer|exists:customer_credits,id',
            ]);
            if ($validated->fails()) {
                return $this->responseHelper->error('Validation Error', $validated->errors());
            }
            $customer = $this->account->customers()->where('id', $data['customer_id'])->first();
            $credit   = $this->account->customerCredits()->where('id', $data['credit_id'])->first();
            $result   = CustomerInvoice::dispatch($customer, $credit);

            if (! $result) {
                return $this->responseHelper->error($result, 'error, could not send the customer an invoice');
            }

            return $this->responseHelper->respond($result, 'Success, customer invoice sent successfully.');
        } catch (Exception $e) {
            info($e->getMessage());
            return $this->responseHelper->error($e->getMessage(), 'error, could not send this invoice.');
        }
    }
}
