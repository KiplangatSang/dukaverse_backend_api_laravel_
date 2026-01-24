<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'order_id',
        'rider_id',
        'category_id',
        'status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'pickup_location',
        'delivery_location',
        'estimated_distance',
        'estimated_duration',
        'actual_distance',
        'actual_duration',
        'fee',
        'rating',
        'rider_rating',
        'food_rating',
        'packaging_rating',
        'tip_amount',
        'feedback',
        'special_instructions',
        'notes',
        'ownerable_type',
        'ownerable_id',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'pickup_location' => 'array',
        'delivery_location' => 'array',
        'estimated_distance' => 'decimal:2',
        'estimated_duration' => 'integer',
        'actual_distance' => 'decimal:2',
        'actual_duration' => 'integer',
        'fee' => 'decimal:2',
        'rating' => 'integer',
        'rider_rating' => 'integer',
        'food_rating' => 'integer',
        'packaging_rating' => 'integer',
        'tip_amount' => 'decimal:2',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'rider_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DeliveryCategory::class, 'category_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(DeliveryBid::class);
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Methods
    public function assignRider($rider)
    {
        $this->update([
            'rider_id' => $rider->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
    }

    public function markAsPickedUp()
    {
        $this->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function isAvailableForBidding()
    {
        return $this->status === 'pending';
    }

    public function canBeHandledByVehicle($vehicleType)
    {
        return $this->category && in_array($vehicleType, $this->category->allowed_vehicle_types ?? []);
    }
}
