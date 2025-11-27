<?php

namespace App\Helpers\Billing;

use App\Accounts\Transaction;
use Illuminate\Support\Str;

interface PaymentGatewayContract
{

    public function charge($transaction);
    public function setDiscount($amount);
    public function setCharge($amount);
    public function pay($transaction);
    public function transfer($transaction);
    public function withdraw($transaction);
    public function deposit($transaction);
}
