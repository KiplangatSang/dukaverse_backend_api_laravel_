<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function settings()
    {
        return $this->hasOne(RestaurantSetting::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class)->orderBy('order');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function tableCombinations()
    {
        return $this->hasMany(TableCombination::class);
    }

    public function waitlist()
    {
        return $this->hasMany(Waitlist::class);
    }

    public function tableStatusHistory()
    {
        return $this->hasMany(TableStatusHistory::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function staff()
    {
        return $this->hasManyThrough(Employee::class, 'restaurant_staff', 'restaurant_id', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByOwner($query, $ownerableType, $ownerableId)
    {
        return $query->where('ownerable_type', $ownerableType)
                    ->where('ownerable_id', $ownerableId);
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return $this->address;
    }

    public function getCoordinatesAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    // Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];
}
