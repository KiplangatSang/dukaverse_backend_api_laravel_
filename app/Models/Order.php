<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {
    use HasFactory, SoftDeletes;
    //
    protected $guarded = [];

    const ORDER_PLACED = 'order placed';
    const PROCESSING   = 'processing';
    const CANCELLED    = 'cancelled';
    const PACKED       = 'packed';
    const SHIPPED      = 'shipped';
    const DELIVERED    = 'delivered';

    const ORDERSTATUS = [
        self::ORDER_PLACED,
        self::PROCESSING,
        self::CANCELLED,
        self::PACKED,
        self::SHIPPED,
        self::DELIVERED,
    ];

    const AWAITING_AUTHORIZATION = 'awaiting authorization';
    const FAILED                 = 'failed';
    const UNPAID                 = 'unpaid';
    const PAID                   = 'paid';

    const PAYSTATUS = [
        self::AWAITING_AUTHORIZATION,
        self::FAILED,
        self::UNPAID,
        self::PAID,
    ];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function retails()
    {
        return $this->ownerable();
    }

    public function supplierable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function requiredItems()
    {
        return $this->hasMany(RequiredItem::class, "orders_id");
    }

    public function supplyItems()
    {
        return $this->hasMany(Supply::class, 'order_id');
    }
    public function items()
    {
        return $this->belongsToMany(RetailItem::class, 'orders_retailitems');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'purposeable');
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'order_location_id');
    }

    public function deliveryLocation()
    {
        return $this->belongsTo(Location::class, 'delivery_location_id');
    }

    // protected $dispatchesEvents = [
    //     'created' => OrderPlaced::class,
    //     'update' => OrderPlaced::class,
    //     'deleted' => OrderPlaced::class,
    // ];
}
