<?php
namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
        'features' => JsonCasing::class,
    ];

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function currentReservation()
    {
        return $this->hasOne(Reservation::class)
            ->whereIn('status', ['confirmed', 'seated'])
            ->where('reservation_datetime', '>=', now()->subHours(2))
            ->where('reservation_datetime', '<=', now()->addHours(2));
    }

    public function statusHistory()
    {
        return $this->hasMany(TableStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    public function combinations()
    {
        return $this->belongsToMany(TableCombination::class, 'combination_table')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function activeCombination()
    {
        return $this->combinations()->where('status', 'active')->first();
    }

    public function waitlistEntries()
    {
        return $this->hasMany(Waitlist::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    public function scopeByCapacity($query, $minCapacity, $maxCapacity = null)
    {
        $query->where('capacity', '>=', $minCapacity);

        if ($maxCapacity) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query;
    }

    // Accessors
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    public function getIsOccupiedAttribute()
    {
        return $this->status === 'occupied';
    }

    public function getIsReservedAttribute()
    {
        return $this->status === 'reserved';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'available' => 'green',
            'occupied' => 'red',
            'reserved' => 'yellow',
            'cleaning' => 'blue',
            'maintenance' => 'orange',
            'out_of_order' => 'gray',
            default => 'gray',
        };
    }

    // Methods
    public function canAccommodate($partySize)
    {
        return $this->capacity >= $partySize;
    }

    public function markAsOccupied($notes = null, $relatedModel = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'occupied',
            'notes' => $notes,
        ]);

        // Log status change
        $this->logStatusChange($previousStatus, 'occupied', $relatedModel, $notes);
    }

    public function markAsAvailable($notes = null, $relatedModel = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'available',
            'notes' => $notes,
        ]);

        // Log status change
        $this->logStatusChange($previousStatus, 'available', $relatedModel, $notes);
    }

    public function reserve($notes = null, $relatedModel = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'reserved',
            'notes' => $notes,
        ]);

        // Log status change
        $this->logStatusChange($previousStatus, 'reserved', $relatedModel, $notes);
    }

    public function markAsCleaning($notes = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'cleaning',
            'notes' => $notes,
        ]);

        $this->logStatusChange($previousStatus, 'cleaning', null, $notes);
    }

    public function markAsMaintenance($notes = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'maintenance',
            'notes' => $notes,
        ]);

        $this->logStatusChange($previousStatus, 'maintenance', null, $notes);
    }

    public function markAsOutOfOrder($notes = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'out_of_order',
            'notes' => $notes,
        ]);

        $this->logStatusChange($previousStatus, 'out_of_order', null, $notes);
    }

    public function updateStatus($newStatus, $notes = null, $relatedModel = null, $userId = null)
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => $newStatus,
            'notes' => $notes,
        ]);

        $this->logStatusChange($previousStatus, $newStatus, $relatedModel, $notes, $userId);
    }

    protected function logStatusChange($previousStatus, $newStatus, $relatedModel = null, $notes = null, $userId = null)
    {
        $data = [
            'table_id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $userId ?? auth()->id(),
            'changed_at' => now(),
        ];

        if ($relatedModel) {
            $data['related_type'] = get_class($relatedModel);
            $data['related_id'] = $relatedModel->id;
        }

        TableStatusHistory::create($data);
    }

    public function isCombined()
    {
        return $this->activeCombination() !== null;
    }

    public function getOccupiedDuration()
    {
        $latestOccupied = $this->statusHistory()
            ->where('new_status', 'occupied')
            ->first();

        if (!$latestOccupied) {
            return null;
        }

        return $latestOccupied->changed_at->diffForHumans(null, true);
    }

    // Constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_RESERVED = 'reserved';
    const STATUS_CLEANING = 'cleaning';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_OUT_OF_ORDER = 'out_of_order';

    const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_OCCUPIED,
        self::STATUS_RESERVED,
        self::STATUS_CLEANING,
        self::STATUS_MAINTENANCE,
        self::STATUS_OUT_OF_ORDER,
    ];

    const SECTIONS = [
        'main',
        'patio',
        'private',
        'bar',
        'lounge',
        'outdoor',
    ];
}
