<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'delivery_id',
        'delivery_person_id',
        'base_fee',
        'distance_fee',
        'time_fee',
        'tip_amount',
        'bonus_amount',
        'total_earned',
        'platform_fee',
        'tax_amount',
        'insurance_fee',
        'total_deductions',
        'net_earnings',
        'payment_status',
        'paid_at',
        'payment_reference',
        'earning_date',
        'period',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'distance_fee' => 'decimal:2',
        'time_fee' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_earnings' => 'decimal:2',
        'paid_at' => 'datetime',
        'earning_date' => 'date',
    ];

    // Relationships
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(DeliveryPerson::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('payment_status', 'processing');
    }

    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('earning_date', $date);
    }

    // Methods
    public function markAsPaid($reference = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $reference,
        ]);
    }

    public function markAsProcessing()
    {
        $this->update(['payment_status' => 'processing']);
    }

    public function markAsFailed()
    {
        $this->update(['payment_status' => 'failed']);
    }

    public function calculateNetEarnings()
    {
        $this->net_earnings = $this->total_earned - $this->total_deductions;
        $this->save();
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }
}
