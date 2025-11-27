<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function methodable()
    {
        return $this->morphTo();
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

}
