<?php

namespace App\Helpers\Billing;

use App\Accounts\Transaction;
use App\Helpers\Billing\Charging\DukaVerseCharges;
use App\Helpers\Billing\Charging\DukaverseDiscount;
use App\Repositories\AppRepository;
use Illuminate\Support\Str;

class Charges
{
    private $paymentGateway;

    public function __construct(PaymentGatewayContract  $paymentGateway)
    {

        $this->paymentGateway = $paymentGateway;
    }

    public function all($gateway,$transaction, $customer)
    {
        # code...
        $amount = $transaction['amount'];
        $discount = null;
        $charge = null;

        switch ($gateway) {
            case "DUKAVERSE":
                //discount
                $discount_class = new DukaverseDiscount();
                $discount =  $discount_class->discount($amount);
                $amount -= $discount;
                $charge_class = new DukaVerseCharges();
                $charge =  $charge_class->charge($amount);

                break;
            default:
                $charge = 0;
        }
        $this->paymentGateway->setDiscount($discount);
        $this->paymentGateway->setCharge($charge);

        $apprepo = new AppRepository();
        $location = (array)$apprepo->getLocation();

        return [
            "name" => $customer['name'] ?? $customer['phone_number'] ,
            'address' =>  $location ?? $customer['phone_number'] ,
            'amount' => $transaction['amount'],
            'discount' => $discount,
            "charge" => $charge,
            'charged_amount' => $amount,
            'gateway' => $gateway,
        ];
    }
}
