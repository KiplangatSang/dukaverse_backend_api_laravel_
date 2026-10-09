<?php

namespace App\Services;

use App\Http\Resources\ResponseHelper;
use App\Http\Resources\StoreFileResource;
use App\Models\Transaction;

class PaymentDoneService extends BaseService
{

    public function __construct(
        protected readonly StoreFileResource $storeFileResource,
        protected readonly ResponseHelper $responseHelper,
    ) {
        parent::__construct($storeFileResource, $responseHelper);
    }


    //check type of transaction
    public function index(Transaction $transaction): bool
    {
        # code...
        /*
         * transaction_type= 1=>internal within dukaverse accounts paying for Dukaverse Products ie loans payment
         *                   2=>internal within the dukaverse accounts but not paying for dukaverse products ie deposit,transfer
         *                   3=>external and the money is not involded with dukaverse ie cash payments(just to help keep track)
         * message = message tied up with sending the transaction
         */
        $gateway = $transaction->gateway;
        info('gateway' . $gateway);
        switch ($gateway) {
            case "DUKAVERSE":
                $senderupdate = $this->calculateSenderUpdate($transaction);
                $receiverupdate = $this->calculateReceiverUpdate($transaction);
                break;
            case "MPESA":
                $senderupdate = $this->calculateReceiverUpdate($transaction);
                $receiverupdate = true;
                break;
        }

        if ($senderupdate && $receiverupdate) {
            return true;
        } else {
            return false;
        }

        $updatePaidItem =  $this->updatePaidItem($transaction);

        if (!$updatePaidItem) {
            return false;
        }

        return true;
    }

    public function updatePaidItem($transaction)
    {
        # code...
        if (!$transaction->purposable) {
            return true;
        }

        $paidItem = $transaction->purposable;
        $paidItem->update([
            'pay_status' => true,
        ]);
        return true;
    }

    public function pollMpesaPaymentResult($payment_checkout_id)
    {
        $transaction = Transaction::where('transaction_id', $payment_checkout_id,)->first();
        if (!$transaction) {
            return $this->responseHelper->error("Transaction could not be found.", error: ["transaction" => "Transaction could not be found", 404]);
        }

        if ($transaction->status = Transaction::PAID) {
            return $this->responseHelper->respond(["transaction" => $transaction], "Payment done successfully");
        } else if ($transaction->status == Transaction::FAILED) {
            return $this->responseHelper->error("Payment failed", ["error" => "The payment could not be processed.", 500]);
        } else if ($transaction->status == Transaction::PENDING) {
            return $this->responseHelper->respond(["transaction" => $transaction], "The payment is pending");
        } else if ($transaction->status == Transaction::REFUNDED) {
            return $this->responseHelper->respond(["transaction" => $transaction], "Payment has been refunded");
        } else {
            return $this->responseHelper->error("Payment tracking failed", ["error" => "The payment status could not be tracked.", 500]);
        }
    }



    public function calculateSenderUpdate($transaction)
    {
        # code...
        $account = $transaction->senderAccount;
        $initial_amount = $account->balance;
        $amount_to_deduct = $transaction->total_amount;
        $new_amount = $initial_amount - $amount_to_deduct;

        return $this->updateAccount($account, $new_amount);
    }
    public function calculateReceiverUpdate($transaction)
    {
        # code...
        $account = $transaction->receiverAccount;
        $initial_amount = $account->balance;
        $amount_to_add = $transaction->total_amount;
        $new_amount = $initial_amount + $amount_to_add;
        return $this->updateAccount($transaction->receiverAccount, $new_amount);
    }

    public function updateAccount($account, $amount)
    {
        # code...
        $update = $account->update(
            ["balance" => $amount]
        );

        if (!$update) {
            return false;
        }

        return $update;
    }
}
