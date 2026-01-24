<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'allowed_vehicle_types',
        'max_weight',
        'max_dimensions',
        'base_fee',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'allowed_vehicle_types' => 'array',
        'max_weight' => 'decimal:2',
        'max_dimensions' => 'array',
        'base_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderBySort($query)
    {
        return $query->orderBy('sort_order');
    }

    // Methods
    public function allowsVehicleType($vehicleType)
    {
        return in_array($vehicleType, $this->allowed_vehicle_types ?? []);
    }

    public function isCompatibleWithVehicle($vehicle)
    {
        return $this->allowsVehicleType($vehicle->vehicle_type);
    }
}
