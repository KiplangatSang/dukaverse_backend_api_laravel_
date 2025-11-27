<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequiredItem extends Model {
    use HasFactory, SoftDeletes;

    protected $casts = [
        'created_at' => DateTimeCasting::class,
    ];

    protected $guarded = [];

    public function requiredable()
    {
        return $this->morphTo();
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->belongsTo(RetailItem::class, 'retail_item_id');
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public static function createRequiredItem(Retail $retail, RetailItem $retail_item, $amount)
    {
        $retail_item->is_required = true;
        $retail_item->save();

        $result = $retail->requiredItems()->create(
            ["retail_item_id" => $retail_item->id,
                "required_amount" => $amount,
                "projected_cost"  => $retail_item->buying_price * $amount]
        );

    }

}
