<?php

namespace App\Helpers\Billing;

use Illuminate\Support\Str;

class CreditPaymentGateway implements PaymentGatewayContract
{
    protected $gateway;

    public $purposable_type, $purposable_id = null;
    private $currency = null;
    private $trans_type = null;
    private $discount = null;
    private $charge = null;

    public function __construct($currency,$trans_type)
    {
        //  $this->setPurposeable();

        $this->currency =  $currency;
        $this->trans_type=  $trans_type;
    }


    public function charge($amount)
    {

        return [
            'amount' =>  $amount - $this->discount,
            'confirmation_number' => STR::random(10),
            'currency' => $this->currency,
            'discount' => $this->discount,
            'fee' => $amount * 0.03,
            'Transaction_Type' => $this->trans_type,
        ];
    }
    public function setCharge($amount)
    {
        # code...
        $this->charge = $amount;
    }

    public function pay($transaction)
    {

    }

    public function setDiscount($amount)
    {
        # code...
        $this->discount = $amount;
    }

    public function transfer($transaction){}
    public function withdraw($transaction){}
    public function deposit($transaction){}
}
