<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        "meta_data"  => JsonCast::class,
        "regulation" => JsonCast::class,
    ];

    const ECOMMERCE_GATEWAYS = [
        'stripe'          => 'Stripe',
        'paypal'          => 'PayPal',
        'bank_transfer'   => 'Bank Transfer',
        'pay_on_delivery' => 'Pay On Delivery',
        'razorpay'        => 'RazorPay',
        'paystack'        => 'PayStack',
        'mpesa'           => 'MPesa',
    ];

    const CASH_ON_DELIVERY = [
        'type' => 'pay_on_delivery',
        "name" => 'Pay On Delivery',
    ];

    const MPESA = [
        'type' => 'MPesa',
        "name" => 'MPesa',
    ];

    const PRODUCTION_ECOMMERCE_GATEWAYS = [
        self::MPESA,
        self::CASH_ON_DELIVERY,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

}
