<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleTransaction extends Model {
    use HasFactory, SoftDeletes;

    //
    protected $guarded = [];

    const SALE_TRANSACTION_ACTIVE   = "active";
    const SALE_TRANSACTION_HOLD     = "hold";
    const SALE_TRANSACTIIN_COMPLETE = "complete";

    const TRANSACTION_STATUS = [
        self::SALE_TRANSACTION_ACTIVE,
        self::SALE_TRANSACTION_HOLD,
        self::SALE_TRANSACTIIN_COMPLETE,
    ];

    const AWAITING_AUTHORIZATION = 'awaiting authorization';
    const FAILED                 = 'failed';
    const UNPAID                 = 'not paid';
    const PAID                   = 'paid';
    const AWAITING_REFUND        = 'awaiting refund';

    const REFUNDED = 'refunded';

    const PAYSTATUS = [
        self::AWAITING_AUTHORIZATION,
        self::FAILED,
        self::UNPAID,
        self::PAID,
        self::AWAITING_REFUND,
        self::REFUNDED,
    ];

    const DIRECT    = "direct";
    const AFFILIATE = "affiliate";
    const SPONSORED = "sponsored";

    const SALES_TYPES = [
        self::DIRECT,
        self::AFFILIATE,
        self::SPONSORED,
    ];

    const CREDIT        = "credit";
    const HIRE_PURCHASE = "hire_purchase";
    const CASH          = "cash";

    const PAYMENT_TYPE = [
        self::CREDIT,
        self::HIRE_PURCHASE,
        self::CASH,
    ];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function transactionable()
    {
        # code...
        return $this->morphTo();
    }

    public function user()
    {
        # code...
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sales()
    {
        # code...
        return $this->hasMany(Sale::class, 'sale_transaction_id');
    }

    public function items()
    {
        # code...
        return $this->belongsToMany(RetailItem::class, 'saletransactions_retailitems');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function credit()
    {
        return $this->hasOne(CustomerCredit::class, 'sale_transaction_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'sale_transaction_id');
    }

}
