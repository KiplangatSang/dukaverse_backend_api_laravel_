<?php

namespace App\Helpers\Billing\Charging;

use App\Helpers\Billing\PaymentGatewayContract;
use App\Repositories\AppRepository;
use Illuminate\Support\Str;

class DukaVerseCharges
{

    protected $currency,$discount,$trans_type;
    public function charge($amount)
    {
        # code...
        $charge = null;

        switch ($amount) {
            case $amount <= 1000:
                $charge = 0;
                break;
            case $amount > 1000 && $amount <= 5000:
                $charge = 100;
                break;
            case $amount > 5000 && $amount <= 10000:
                $charge = 150;
                break;
            case $amount > 10000 && $amount <= 25000:
                $charge = 200;
                break;
            case $amount > 25000 && $amount <= 50000:
                $charge = 250;
                break;
            case $amount > 50000 && $amount <= 100000:
                $charge = 300;
                break;
            case $amount > 100000 && $amount <= 250000:
                $charge = 350;
                break;
            case $amount > 250000 && $amount <= 500000:
                $charge = 400;
                break;
            case $amount > 500000 && $amount <= 700000:
                $charge = 450;
                break;
            case $amount > 700000:
                $charge = 0;
                break;
            default:
                $charge = 0;
        }
        return $charge;
    }

    public function  depositCharges($amount)
    {
        # code...

        $charge = 0;
        switch ($amount) {
            case $amount <= 1000:
                $charge = 0;
                break;
            case $amount > 1000 && $amount <= 5000:
                $charge = 100;
                break;
            case $amount > 5000 && $amount <= 10000:
                $charge = 150;
                break;
            case $amount > 10000 && $amount <= 25000:
                $charge = 200;
                break;
            case $amount > 25000 && $amount <= 50000:
                $charge = 250;
                break;
            case $amount > 50000 && $amount <= 100000:
                $charge = 300;
                break;
            case $amount > 100000 && $amount <= 250000:
                $charge = 350;
                break;
            case $amount > 250000 && $amount <= 500000:
                $charge = 400;
                break;
            case $amount > 500000 && $amount <= 700000:
                $charge = 450;
                break;
            case $amount > 700000:
                $charge = 0;
                break;
            default:
                $charge = 0;
        }

        return $charge;
    }

    public function withdrawalCharges($amount)
    {
        # code...

        $charge = 0;
        switch ($amount) {
            case $amount <= 1000:
                $charge = 0;
                break;
            case $amount > 1000 && $amount <= 5000:
                $charge = 100;
                break;
            case $amount > 5000 && $amount <= 10000:
                $charge = 150;
                break;
            case $amount > 10000 && $amount <= 25000:
                $charge = 200;
                break;
            case $amount > 25000 && $amount <= 50000:
                $charge = 250;
                break;
            case $amount > 50000 && $amount <= 100000:
                $charge = 300;
                break;
            case $amount > 100000 && $amount <= 250000:
                $charge = 350;
                break;
            case $amount > 250000 && $amount <= 500000:
                $charge = 400;
                break;
            case $amount > 500000 && $amount <= 700000:
                $charge = 450;
                break;
            case $amount > 700000:
                $charge = 0;
                break;
            default:
                $charge = 0;

                return $charge;
        }
    }


    public function chargeOld($transaction)
    {

        $amount  = $transaction->total_amount;
        $charge = 0;
        switch ($amount) {
            case $amount <= 1000:
                $charge = 0;
                break;
            case $amount > 1000 && $amount <= 5000:
                $charge = 100;
                break;
            case $amount > 5000 && $amount <= 10000:
                $charge = 150;
                break;
            case $amount > 10000 && $amount <= 25000:
                $charge = 200;
                break;
            case $amount > 25000 && $amount <= 50000:
                $charge = 250;
                break;
            case $amount > 50000 && $amount <= 100000:
                $charge = 300;
                break;
            case $amount > 100000 && $amount <= 250000:
                $charge = 350;
                break;
            case $amount > 250000 && $amount <= 500000:
                $charge = 400;
                break;
            case $amount > 500000 && $amount <= 700000:
                $charge = 450;
                break;
            case $amount > 700000:
                $charge = 0;
                break;
            default:
                $charge = 0;
        }

        $amount =  ($amount + $charge) - $this->discount;

        return [
            'gateway' => $transaction->gateway,
            'charge' => $charge,
            'amount' =>  $amount,
            'confirmation_number' => STR::random(10),
            'currency' => $this->currency,
            'discount' => $this->discount,
            'status' => "success",
            'Transaction_Type' => $this->trans_type,
        ];
        # code...
    }
}
