<?php

namespace App\Helpers\Billing\Charging;

use App\Helpers\Billing\PaymentGatewayContract;
use App\Repositories\AppRepository;
use Illuminate\Support\Str;

class DukaverseDiscount
{

    public function discount($amount)
    {
        # code...
        $discount = null;

        switch ($amount) {
            case $amount <= 1000:
                $discount = 0;
                break;
            case $amount > 1000 && $amount <= 5000:
                $discount = 100;
                break;
            case $amount > 5000 && $amount <= 10000:
                $discount = 150;
                break;
            case $amount > 10000 && $amount <= 25000:
                $discount = 200;
                break;
            case $amount > 25000 && $amount <= 50000:
                $discount = 250;
                break;
            case $amount > 50000 && $amount <= 100000:
                $discount = 300;
                break;
            case $amount > 100000 && $amount <= 250000:
                $discount = 350;
                break;
            case $amount > 250000 && $amount <= 500000:
                $discount = 400;
                break;
            case $amount > 500000 && $amount <= 700000:
                $discount = 450;
                break;
            case $amount > 700000:
                $discount = 0;
                break;
            default:
                $discount = 0;
        }
        return $discount;
    }
}
