<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCredit extends Model {
    use HasFactory, SoftDeletes;

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
    ];

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function creditable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->morphOne(Customer::class, 'customerable');
    }

    public function saleTransaction()
    {

        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }
}
