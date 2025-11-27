<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceProduct extends Model {
    use HasFactory, SoftDeletes;

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function ecommerce()
    {
        return $this->belongsTo(Ecommerce::class, 'ecommerce_id');
    }
}
