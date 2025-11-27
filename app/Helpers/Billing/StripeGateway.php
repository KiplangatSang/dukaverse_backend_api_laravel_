<?php

namespace App\Helpers\Billing;

use App\Accounts\Transaction;
use Illuminate\Support\Str;

class StripeGateway
{
    public function stripetest()
    {
        # code...
        //set stripe client code
        $stripe = new \Stripe\StripeClient(env("stripe_client_key"));

        $stripe->paymentIntents->create(
            ['amount' => 500, 'currency' => 'gbp', 'payment_method' => 'pm_card_visa']
        );
    }
}
