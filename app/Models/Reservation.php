<?php

namespace App\Models;

use App\Casts\DateTimeCasting;
use App\Helpers\JsonCasing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => DateTimeCasting::class,
        'updated_at' => DateTimeCasting::class,
        'deleted_at' => DateTimeCasting::class,
        'reservation_datetime' => 'datetime',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deposit_paid_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'table_preferences' => JsonCasing::class,
        'notification_preferences' => JsonCasing::class,
        'deposit_required' => 'boolean',
        'deposit_paid' => 'boolean',
        'refund_issued' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->uuid)) {
                $reservation->uuid = (string) Str::uuid();
            }
            if (empty($reservation->confirmation_code)) {
                $reservation->confirmation_code = strtoupper(Str::random(6));
            }
            if (empty($reservation->reservation_number)) {
                $reservation->reservation_number = 'RSV-' . date('Y') . '-' . str_pad(
                    Reservation::whereYear('created_at', date('Y'))->count() + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
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

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeSeated($query)
    {
        return $query->where('status', 'seated');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeNoShow($query)
    {
        return $query->where('status', 'no_show');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('reservation_datetime', '>=', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_datetime', today());
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('reservation_datetime', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reservation_datetime', [$startDate, $endDate]);
    }

    public function scopeByTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    public function scopeByCustomerPhone($query, $phone)
    {
        return $query->where('customer_phone', $phone);
    }

    // Accessors
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsConfirmedAttribute()
    {
        return $this->status === 'confirmed';
    }

    public function getIsSeatedAttribute()
    {
        return $this->status === 'seated';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsNoShowAttribute()
    {
        return $this->status === 'no_show';
    }

    public function getIsUpcomingAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->reservation_datetime >= now();
    }

    public function getIsPastAttribute()
    {
        return $this->reservation_datetime < now();
    }

    public function getTimeUntilReservationAttribute()
    {
        if ($this->reservation_datetime < now()) {
            return null;
        }

        return now()->diffForHumans($this->reservation_datetime, true);
    }

    // Methods
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    public function checkIn($userId = null, $actualPartySize = null)
    {
        $this->update([
            'status' => 'seated',
            'checked_in_at' => now(),
            'checked_in_by' => $userId,
            'actual_party_size' => $actualPartySize ?? $this->party_size,
        ]);

        // Update table status
        if ($this->table) {
            $this->table->markAsOccupied("Reservation {$this->reservation_number} checked in");
        }
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
    }

    public function cancel($reason = null, $notes = null, $userId = null, $refundDeposit = false)
    {
        $updateData = [
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancellation_notes' => $notes,
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
        ];

        if ($refundDeposit && $this->deposit_paid) {
            $updateData['refund_issued'] = true;
            $updateData['refund_amount'] = $this->deposit_amount;
        }

        $this->update($updateData);

        // Free up the table if reserved
        if ($this->table && $this->table->status === 'reserved') {
            $this->table->markAsAvailable("Reservation {$this->reservation_number} cancelled");
        }
    }

    public function markAsNoShow($userId = null)
    {
        $this->update([
            'status' => 'no_show',
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
        ]);

        // Free up the table
        if ($this->table && $this->table->status === 'reserved') {
            $this->table->markAsAvailable("Reservation {$this->reservation_number} - no show");
        }
    }

    public function sendReminder()
    {
        // TODO: Implement notification sending
        $this->update(['reminder_sent_at' => now()]);
    }

    // Constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SEATED = 'seated';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_SEATED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];
}
