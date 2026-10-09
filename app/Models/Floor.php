<?php

namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'elements' => JsonCasing::class,
        'is_active' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    // Scopes
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Accessors
    public function getTableCountAttribute()
    {
        return $this->tables()->count();
    }

    public function getTotalCapacityAttribute()
    {
        return $this->tables()->sum('capacity');
    }

    public function getAvailableTablesCountAttribute()
    {
        return $this->tables()->where('status', 'available')->count();
    }

    public function getOccupiedTablesCountAttribute()
    {
        return $this->tables()->where('status', 'occupied')->count();
    }

    // Methods
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }
}
