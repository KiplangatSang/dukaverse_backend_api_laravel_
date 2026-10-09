<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes, NodeTrait;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'availability_schedule' => JsonCasing::class,
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class, 'category_id');
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

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $names = [];
        $category = $this;

        while ($category) {
            array_unshift($names, $category->name);
            $category = $category->parent;
        }

        return implode(' > ', $names);
    }

    // Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];
}
