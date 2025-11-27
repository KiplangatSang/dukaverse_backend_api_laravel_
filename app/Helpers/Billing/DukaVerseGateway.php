<?php

namespace App\Helpers\Billing;

use App\Events\PaymentDone;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class DukaVerseGateway implements PaymentGatewayContract
{
    protected $gateway;


    public $purposable_type, $purposable_id = null;
    private $currency = null;
    private $trans_type = null;
    private $discount = null;
    private $charges = null;

    public function __construct($currency, $trans_type)
    {
        $this->trans_type =  $trans_type;
        $this->currency =  $currency;
    }

    public function makeReceiptStamp($transaction)
    {
        # code...
        $response['amount']  = $transaction->total_amount;
        $response['DukaverseReceiptNumber']  = STR::random(12);
        $response['transactionDate']  = now();
        $response['phoneNumber']  = $transaction->receiverAccount->phone_number;
        $response['desc'] = "Transaction processed successfully";
        return  $response;
    }

    public function charge($transaction)
    {
        $sender = $transaction->senderAccount;
        $receiver =  $transaction->receverAccount;
        //2123974, 2123735, 2121048

        if (floatval($sender->balance) <= floatval($transaction->total_amount)) {
            return false;
        }

        $cost = $this->charges;
        $amount = $cost + $transaction->total_amount;
        $response = $this->makeReceiptStamp($transaction);
        $transaction->updateOrCreate(
            [
                'trans_id' => $transaction->trans_id,
            ],
            [
                'transaction_response' => $response['desc'],
                'transaction_meta' => json_encode($response),
                'cost' => $cost,
                'status' => true,
                'total_amount' => $amount,
            ]
        );

        PaymentDone::dispatch($transaction);
        return $response;
    }

    public function pay($transaction)
    {
        $sender = $transaction->senderAccount;
        $receiver =  $transaction->receverAccount;
        if (!$sender->balance >= $transaction->total_amount)
            return false;
        $cost = $this->charges;
        dd($cost);
        $amount = $cost + $transaction->total_amount;
        $response = $this->makeReceiptStamp($transaction);
        $transaction->updateOrCreate(
            [
                'trans_id' => $transaction->trans_id,
            ],
            [
                'transaction_response' => $response['desc'],
                'transaction_meta' => json_encode($response),
                'cost' => $cost,
                'status' => true,
                'total_amount' => $amount,
            ]
        );

        PaymentDone::dispatch($transaction);
    }

    public function setDiscount($amount)
    {
        # code...
        $this->discount = $amount;
    }

    public function setCharge($amount)
    {
        # code...
        return $this->charges = $amount;
    }
    public function transfer($transaction)
    {
        $sender = $transaction->senderAccount;
        $receiver =  $transaction->receverAccount;
        if (!$sender->balance >= $transaction->total_amount)
            return false;

        $response = $this->makeReceiptStamp($transaction);
        $transaction->updateOrCreate(
            [
                'trans_id' => $transaction->trans_id,
            ],
            [
                'transaction_response' => $response['desc'],
                'transaction_meta' => json_encode($response),
                'status' => true,
            ]
        );

        PaymentDone::dispatch($transaction);
    }
    public function withdraw($transaction)
    {
        // $sender = $transaction->senderAccount;
        // $receiver =  $transaction->receverAccount;
        // if (!$sender->balance >= $transaction->total_amount)
        //     return false;

        // $response = $this->makeReceiptStamp($transaction);
        // $transaction->updateOrCreate(
        //     [
        //         'trans_id' => $transaction->trans_id,
        //     ],
        //     [
        //         'transaction_response' => $response['desc'],
        //         'transaction_meta' => json_encode($response),
        //         'status' => true,
        //     ]
        // );

    }
    public function deposit($transaction)
    {
        $sender = $transaction->senderAccount;
        $receiver =  $transaction->receverAccount;
        if (!$sender->balance >= $transaction->total_amount)
            return false;

        $response = $this->makeReceiptStamp($transaction);
        $transaction->updateOrCreate(
            [
                'trans_id' => $transaction->trans_id,
            ],
            [
                'transaction_response' => $response['desc'],
                'transaction_meta' => json_encode($response),
                'status' => true,
            ]
        );

        PaymentDone::dispatch($transaction);
    }
}
