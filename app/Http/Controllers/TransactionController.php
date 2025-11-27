<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Repositories\TransactionsRepository;
use App\Services\AuthService;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{

    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }
    /**
     * Display a listing of the resource.
     */

    protected function transactionRepository()
    {
        $retail          = $this->getAccount();
        $transactionRepo = new TransactionsRepository($retail);
        return $transactionRepo;
    }

    public function index()
    {
        //

        $transactions                    = $this->transactionRepository()->getTransactions();
        $transactiondata['transactions'] = $transactions;
        $transactiondata['amount']       = $transactions->sum('total_amount');
        return $this->sendResponse($transactiondata, 'List of transactions');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate(
            [
                'gateway'             => ['required'],
                'receiver_account_id' => ['required'],
                'amount'              => ['required'],
                'transaction_type'    => ['required'],
                'cost'                => ['required'],
                'currency'            => ['required'],
                'purpose'             => ['required'],

            ]
        );

        $transactiondata = $this->storeTransaction($request);

        return $transactiondata;
    }

    public function storeTransaction(Request $request)
    {
        # code...
        $transactiondata = $this->transactionRepository()->saveTransaction(
            $request->gateway,
            $request->sender_account_id,
            $request->receiver_account_id,
            $request->amount,
            $request->message,
            $request->transaction_type,
            $request->cost,
            $request->currency,
            $request->purpose,
            $request->sender_phone_number,
            $request->receiver_phone_number,
            $request->purpose_id,
        );

        return $transactiondata;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        {
            //
            $transaction                    = $this->transactionRepository()->getTransaction($id);
            $transactiondata['transaction'] = $transaction;
            return $this->sendResponse($transactiondata, "Transaction fetched successfully");
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
