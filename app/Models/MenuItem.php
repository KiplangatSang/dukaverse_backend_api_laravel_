<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'images' => JsonCasing::class,
        'allergens' => JsonCasing::class,
        'dietary_info' => JsonCasing::class,
        'tags' => JsonCasing::class,
        'ingredients' => JsonCasing::class,
        'nutritional_info' => JsonCasing::class,
        'availability_schedule' => JsonCasing::class,
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function customizations()
    {
        return $this->hasMany(MenuItemCustomization::class);
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByOwner($query, $ownerableType, $ownerableId)
    {
        return $query->where('ownerable_type', $ownerableType)
                    ->where('ownerable_id', $ownerableId);
    }

    // Accessors
    public function getProfitMarginAttribute()
    {
        if (!$this->cost_price || $this->cost_price == 0) {
            return null;
        }

        return (($this->price - $this->cost_price) / $this->price) * 100;
    }

    public function getHasCustomizationsAttribute()
    {
        return $this->customizations()->exists();
    }

    public function getFullNameAttribute()
    {
        return $this->category ? $this->category->name . ' > ' . $this->name : $this->name;
    }

    // Methods
    public function isAvailableNow()
    {
        if (!$this->is_available) {
            return false;
        }

        if (!$this->availability_schedule) {
            return true;
        }

        // Check availability schedule logic here
        // This would check current time against schedule
        return true; // Placeholder
    }

    public function calculateCustomizationPrice($customizationData)
    {
        $totalPrice = $this->price;

        if (isset($customizationData['options'])) {
            foreach ($customizationData['options'] as $option) {
                if (isset($option['price_modifier'])) {
                    $totalPrice += $option['price_modifier'];
                }
            }
        }

        return $totalPrice;
    }

    // Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DISCONTINUED = 'discontinued';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DISCONTINUED,
    ];
}
