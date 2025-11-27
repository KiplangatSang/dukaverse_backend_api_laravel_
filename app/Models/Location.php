<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model {
    use HasFactory, SoftDeletes;

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function locationable()
    {
        return $this->morphTo();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'order_location_id');
    }

    public function orderDeliveries()
    {
        return $this->hasMany(Order::class, 'delivery_location_id');
    }
}
