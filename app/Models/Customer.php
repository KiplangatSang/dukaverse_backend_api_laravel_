<?php
namespace App\Models;

use App\Helpers\DateTimeCasting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model {
    use HasFactory, SoftDeletes;

    //

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
    ];
    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function retails()
    {
        return $this->morphedByMany(Retail::class, 'customerable');
    }
    public function credits()
    {
        return $this->morphMany(CustomerCredit::class, 'customerable');
    }
    public function saletransactions()
    {
        return $this->hasMany(SaleTransaction::class, 'customer_id');
    }

    public function creditTransactions()
    {
        return $this->hasMany(SaleTransaction::class, 'customer_id')->where('on_credit', true);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'customer_id');
    }

}
