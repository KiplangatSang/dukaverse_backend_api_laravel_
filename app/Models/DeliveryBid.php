<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryBid extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'delivery_id',
        'delivery_person_id',
        'bid_amount',
        'estimated_arrival_time',
        'notes',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'estimated_arrival_time' => 'integer',
        'expires_at' => 'datetime',
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Methods
    public function accept()
    {
        $this->update(['status' => 'accepted']);

        // Assign delivery to this rider
        $this->delivery->assignRider($this->deliveryPerson);

        // Reject other bids
        $this->delivery->bids()
            ->where('id', '!=', $this->id)
            ->update(['status' => 'rejected']);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
