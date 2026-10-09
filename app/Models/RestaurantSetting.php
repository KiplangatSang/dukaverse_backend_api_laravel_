<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'operating_hours' => JsonCasing::class,
        'tax_rate' => 'decimal:4',
        'service_charge_rate' => 'decimal:4',
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
