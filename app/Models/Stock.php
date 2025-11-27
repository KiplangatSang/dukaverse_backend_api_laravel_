<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model {
    use HasFactory, SoftDeletes;

    const STOCKCODELEN = 4;

    protected $casts = [
        'created_at' => DateTimeCasting::class,
    ];

    protected $guarded = [];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function stockable()
    {
        return $this->morphTo();
    }

    public function requiredItems()
    {
        return $this->hasMany(RequiredItem::class);
    }

    public function items()
    {
        return $this->belongsTo(RetailItem::class, 'retail_item_id');
    }

    public function supplierstockable()
    {
        return $this->morphTo();
    }
    // Generate a stock id
    public static function generateStockId(RetailItem $retailItem)
    {

        $stock_id = null;
        do {

            $timestamp = Carbon::now()->format('YmdH');
            $stock_id  = $retailItem->name . $timestamp;

            $parts        = explode(' ', $retailItem->name);
            $originalName = $parts[0]; // Get the first part of the split

            $cleanStockItemName = str_replace(' ', '', $originalName);

            $stock_id = $cleanStockItemName . Str::random(Stock::STOCKCODELEN);

            // Validate the token's uniqueness
            $validator = Validator::make(['stock_id' => $stock_id], [
                'stock_id' => 'required|unique:stocks,code',
            ]);

        } while ($validator->fails());

        return $stock_id;
    }
}
