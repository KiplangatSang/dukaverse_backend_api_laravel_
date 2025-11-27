<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleTransactionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */

    public function index($status = null)
    {
        //on hold
        if ($status == "hold") {
            $transactions = $this->getAccount()
                ->saleTransactions()
                ->where('on_hold', true)
                ->orderBy('updated_at', 'DESC')
                ->with('sales.items')
                ->get();
            return $this->sendResponse($transactions, 'Success, Items on hold retrieved');
        } else if ($status == "complete") {
            //complete
            $transactions = $this->getAccount()->saleTransactions()
                ->where('on_hold', false)
                ->where('is_active', false)
                ->with('sales.items')
                ->orderBy('updated_at', 'DESC')->get();
            return $this->sendResponse($transactions, 'Success, Items on hold retrieved');
        }

    }

    public function generateSaleTransactionId(Request $request)
    {
        $sales_id = null;
        $sales_id = Sales::generateSaleTransactionId();

        if (! $sales_id) {
            return response(['error' => "Error, sales id could not be generated"], 400);
        }
        return response(['sales_id' => $sales_id, 'message' => "success, sales id generated successfully"]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(StoreSaleTransactionRequest $request, $status = null)
    {
        // $validated = $request->validated();

        //completed transaction
        if ($status == SaleTransaction::SALE_TRANSACTIIN_COMPLETE) {
            $transaction = $this->getAccount()
                ->saleTransactions()
                ->where('transaction_id', $request['transaction_id'])
                ->where('user_id', auth()->id())
                ->update(
                    [
                        "paid_amount" => $request['paid_amount'],
                        "balance"     => $request['balance'],
                        "on_hold"     => false,
                        "pay_status"  => true,
                        "is_active"   => false,
                    ]
                );
            return $transaction;
        } else if ($status == SaleTransaction::SALE_TRANSACTION_HOLD) {
            # Hold
            // $validated = $request->validated();
            try {
                $transaction = $this->getAccount()
                    ->saleTransactions()
                    ->updateOrCreate([
                        "transaction_id" => $request['transaction_id'],
                        "user_id"        => auth()->id(),
                    ],
                        [
                            "transaction_amount" => $request['expense'],
                            "paid_amount"        => $request['paid_amount'],
                            "balance"            => $request['paid_amount'] - $request['transaction_amount'],
                            "on_hold"            => true,
                            "pay_status"         => $request['paid_amount'] ? true : false,
                            "is_active"          => false,
                        ]
                    );
                return $transaction;
            } catch (Exception $e) {
                info($e->getMessage());
                return $e->getMessage();
            }
        } else if ($status == SaleTransaction::SALE_TRANSACTION_ACTIVE) {
            //store any transaction
            $transaction = null;
            try { $transaction = $this->getAccount()->saleTransactions()
                    ->updateOrCreate(
                        ["transaction_id" => $request['transaction_id'],
                            "user_id"         => auth()->id(),
                        ],
                        [
                            "on_hold"            => true,
                            "pay_status"         => false,
                            "is_active"          => true,
                            "transaction_amount" => $request['transaction_amount'],
                        ]

                    );
                return $transaction;} catch (Exception $e) {
                info($e->getMessage());
                return $e->getMessage();
            }
        } else {
            //store any transaction
            $transaction = null;
            try { $transaction = $this->getAccount()->saleTransactions()
                    ->updateOrCreate(
                        ["transaction_id" => $request['transaction_id'],
                            "user_id"         => auth()->id(),
                        ],
                        [
                            "on_hold"            => true,
                            "pay_status"         => false,
                            "is_active"          => false,
                            "transaction_amount" => $request['transaction_amount'],
                        ]

                    );
                return $transaction;} catch (Exception $e) {
                info($e->getMessage());
                return $e->getMessage();
            }
            if (! $transaction) {
                return false;
            }

            return $transaction;
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function getItemOnHold($id)
    {
        # code...
        $retail      = $this->getAccount();
        $transaction = $retail->saleTransactions()
            ->where('transaction_id', $id)
            ->where('on_hold', true)
            ->with('sales.items')
            ->first();

        if (! $transaction) {
            return false;
        }

        $transaction->transaction_items = $transaction->sales()->get();
        foreach ($transaction->transaction_items as $item) {
            $item['item'] = $item->items()->first();
        }
        // $transaction['items'] = $items;
        // dd($transaction);
        return $transaction;
    }

    /**
     * Display the specified resource.
     */
    public function show($transaction_id)
    {
        //
        # code...

        $saleTransaction = $this->getAccount()->saleTransactions()
            ->where('transaction_id', $transaction_id)
            ->with('sales.items')
            ->first();

        if (! $saleTransaction) {
            return $this->sendError($saleTransaction, 'Error, transaction could not be fetched.');
        }

        return $this->sendResponse($saleTransaction, 'success, transaction fetched successfully.');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaleTransaction $saleTransaction, $status = null)
    {
        //
        //active $trans_id
        $retail       = $this->getAccount();
        $transactions = null;
        if ($status == "active") {
            $transactions = $retail->saleTransactions()
                ->where('is_active', true)
                ->where('transaction_id', $saleTransaction->trans_id)
                ->with('sales.items')
                ->first();
        } else if ($status == "hold") {
            $transactions = $retail->saleTransactions()
                ->where('transaction_id', $saleTransaction->trans_id)
                ->where('on_hold', true)
                ->with('sales.items')
                ->first();
        } else {
            $transactions = $retail->saleTransactions()
                ->where('transaction_id', $saleTransaction->trans_id)
                ->with('sales.items')
                ->first();

        }
        return $this->sendResponse($transactions, 'Success, Items retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleTransactionRequest $request, $saleTransaction)
    {
        //
        $validated = $request->only([
            "paid_amount",
            "balance",
            "on_hold",
            "pay_status",
            "is_active",
        ]);

        $saleTransaction = $this->getAccount()->saleTransactions()
            ->where('transaction_id', $saleTransaction)
            ->first();
        $result = $saleTransaction->update(
            $validated,
        );

        if (! $result) {
            return $this->sendResponse("Error", 'Error, Item could not be  retrieved');
        }

        $saleTransaction = $this->getAccount()->saleTransactions()
            ->where('id', $saleTransaction->id)
            ->with('sales.items')
            ->first();
        return $this->sendResponse($saleTransaction, 'Success, Item on hold retrieved');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleTransaction $saleTransaction)
    {
        //
    }

    public function closeTransaction(Request $request, $saleTransaction, $flag = null)
    {

        //
        $validated = $request->only([
            "transaction_status",
            "transaction_amount",
            "paid_amount",
        ]);

        $request->validate(["transaction_status" => ["required", 'boolean'],
            "transaction_amount"                     => ["required", "numeric"],
            "paid_amount"                            => ["required", 'numeric']]);

        $saleTransaction = $this->getAccount()->saleTransactions()
            ->where('id', $saleTransaction)
            ->first();

        $employee = $this->employee();

        $result = null;

        if ($validated['transaction_status'] == true) {

            if ($validated['transaction_amount'] == $saleTransaction->transaction_amount
                && $validated['paid_amount'] == $saleTransaction->paid_amount) {

                if (! $saleTransaction->on_credit && $saleTransaction->paid_amount >= $saleTransaction->transaction_amount) {
                    $result = $saleTransaction->update(
                        [
                            'balance'    => 0,
                            'discount'   => $request->discount ? $request->discount : 0,
                            'deductions' => $request->deductions ? $request->deductions : 0,
                            'on_hold'    => false,
                            'pay_status' => true,
                            'is_active'  => false,
                        ]
                    );
                } elseif ($saleTransaction->on_credit) {

                    $result = $saleTransaction->update(
                        [
                            'balance'    => 0,
                            'discount'   => $request->discount ? $request->discount : 0,
                            'deductions' => $request->deductions ? $request->deductions : 0,
                            'on_hold'    => false,
                            'pay_status' => true,
                            'is_active'  => false,
                        ]
                    );
                } elseif ($flag == 'f') {

                    $result = $saleTransaction->update(
                        [
                            'on_hold'   => false,
                            'is_active' => false,
                        ]
                    );
                } else {
                    return $this->sendError('Error, Payment cannot be harmonized', $result);
                }
            } else {
                return $this->sendError('Error, Payment discrepancies need to be harmonized', $result);
            }
        } else {
            $result = $saleTransaction->update(
                $validated,
            );

        }

        $saleTransaction->employee()->associate($employee);
        $result = $saleTransaction->save();

        if (! $result) {
            return $this->sendError($result, 'Error, Item could not be  retrieved');
        }

        $saleTransaction = $this->getAccount()->saleTransactions()
            ->where('id', $saleTransaction->id)
            ->with('sales.items')
            ->first();

        return $this->sendResponse($saleTransaction, 'Success, Item on hold retrieved');
    }

    public function getTransactionWithStatus($transaction_id, $status)
    {

        $transaction = null;
        if ($status == "active") {
            $transaction = $this->getAccount()->saleTransactions()
                ->where('is_active', true)
                ->where('transaction_id', $transaction_id)
                ->with('sales.items')
                ->first();
        }

        return $this->sendResponse($transaction, 'Success, transaction retrieved successfully.');
    }

}
