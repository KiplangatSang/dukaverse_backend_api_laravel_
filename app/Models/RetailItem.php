<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetailItem extends Model {
    use HasFactory, SoftDeletes;
    //
    protected $casts = [
        'created_at'     => DateTimeCasting::class,
        'updated_at'     => DateTimeCasting::class,
        'product_images' => 'array',
    ];

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'retail_item_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'retail_item_id');
    }

    public function requiredItems()
    {
        return $this->hasMany(RequiredItem::class, 'retail_item_id');
    }

    public function supplyItems()
    {
        return $this->morphToMany(Supply::class, 'supplyables');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'orders_retailitems', 'orders_id');
    }
    public function saleTransactions()
    {
        # code...
        return $this->belongsToMany(SaleTransaction::class, 'saletransactions_retailitems', "retailitem_id",
            "saletransaction_id");
    }

    public function media()
    {
        return $this->morphMany(Medium::class, 'mediumable');
    }
}
