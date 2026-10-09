<?php

namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Waitlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'waitlist';

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'deleted_at' => DateTimeCasting::class,
        'notified_at' => 'datetime',
        'seated_at' => 'datetime',
        'table_preferences' => JsonCasing::class,
        'notify_via' => JsonCasing::class,
        'party_size' => 'integer',
        'position' => 'integer',
        'estimated_wait_time' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($waitlist) {
            if (empty($waitlist->uuid)) {
                $waitlist->uuid = (string) Str::uuid();
            }

            // Auto-assign position
            if (empty($waitlist->position)) {
                $maxPosition = Waitlist::where('restaurant_id', $waitlist->restaurant_id)
                    ->where('status', 'waiting')
                    ->max('position');
                $waitlist->position = ($maxPosition ?? 0) + 1;
            }
        });
    }

    // Relationships
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notifiedBy()
    {
        return $this->belongsTo(User::class, 'notified_by');
    }

    public function seatedBy()
    {
        return $this->belongsTo(User::class, 'seated_by');
    }

    // Scopes
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting')->orderBy('position');
    }

    public function scopeNotified($query)
    {
        return $query->where('status', 'notified');
    }

    public function scopeSeated($query)
    {
        return $query->where('status', 'seated');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeNoShow($query)
    {
        return $query->where('status', 'no_show');
    }

    public function scopeByCustomerPhone($query, $phone)
    {
        return $query->where('customer_phone', $phone);
    }

    // Accessors
    public function getIsWaitingAttribute()
    {
        return $this->status === 'waiting';
    }

    public function getIsNotifiedAttribute()
    {
        return $this->status === 'notified';
    }

    public function getIsSeatedAttribute()
    {
        return $this->status === 'seated';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsNoShowAttribute()
    {
        return $this->status === 'no_show';
    }

    // Methods
    public function notify($tableId, $userId = null, $message = null)
    {
        $this->update([
            'status' => 'notified',
            'table_id' => $tableId,
            'notified_at' => now(),
            'notified_by' => $userId,
        ]);

        // TODO: Send actual notification (SMS/Email/Call)
    }

    public function seat($tableId, $userId = null)
    {
        $this->update([
            'status' => 'seated',
            'table_id' => $tableId,
            'seated_at' => now(),
            'seated_by' => $userId,
        ]);

        // Reorder remaining waitlist
        $this->reorderWaitlist();
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
        $this->reorderWaitlist();
    }

    public function markAsNoShow()
    {
        $this->update(['status' => 'no_show']);
        $this->reorderWaitlist();
    }

    protected function reorderWaitlist()
    {
        $waitingEntries = Waitlist::where('restaurant_id', $this->restaurant_id)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->get();

        $position = 1;
        foreach ($waitingEntries as $entry) {
            $entry->update(['position' => $position++]);
        }
    }

    public function updateEstimatedWaitTime($minutes)
    {
        $this->update(['estimated_wait_time' => $minutes]);
    }

    // Constants
    const STATUS_WAITING = 'waiting';
    const STATUS_NOTIFIED = 'notified';
    const STATUS_SEATED = 'seated';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    const STATUSES = [
        self::STATUS_WAITING,
        self::STATUS_NOTIFIED,
        self::STATUS_SEATED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];
}
