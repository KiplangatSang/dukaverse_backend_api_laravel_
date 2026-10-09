<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryPerson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'latitude',
        'longitude',
        'address',
        'city',
        'country',
        'profile_photo',
        'id_photo',
        'license_photo',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'is_accredited',
        'accredited_at',
        'accredited_by',
        'rating',
        'total_deliveries',
        'completed_deliveries',
        'cancelled_deliveries',
        'completion_rate',
        'is_online',
        'is_available',
        'last_online_at',
        'last_delivery_at',
        'delivery_preferences',
        'working_hours',
        'max_delivery_distance',
        'wallet_balance',
        'total_earnings',
        'pending_payments',
        'current_subscription_id',
        'subscription_expires_at',
        'device_token',
        'device_type',
        'app_version',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_accredited' => 'boolean',
        'accredited_at' => 'datetime',
        'rating' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'last_online_at' => 'datetime',
        'last_delivery_at' => 'datetime',
        'delivery_preferences' => 'json',
        'working_hours' => 'json',
        'max_delivery_distance' => 'decimal:2',
        'wallet_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_payments' => 'decimal:2',
        'subscription_expires_at' => 'datetime',
    ];

    protected $appends = ['account_type'];
    public function getAccountTypeAttribute()
    {
        return 'delivery_person';
    }

    // Relationships
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'assigned_rider_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(DeliveryEarning::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(DeliveryVehicle::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(DeliveryBid::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'delivery_person_coupons');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function accreditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accredited_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'current_subscription_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeAccredited($query)
    {
        return $query->where('is_accredited', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // Methods
    public function verify(User $verifier = null)
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifier?->id,
            'status' => 'active',
        ]);
    }

    public function accredit(User $accrediter = null)
    {
        $this->update([
            'is_accredited' => true,
            'accredited_at' => now(),
            'accredited_by' => $accrediter?->id,
        ]);
    }

    public function updateStatus(string $status)
    {
        $this->update(['status' => $status]);
    }

    public function goOnline()
    {
        $this->update([
            'is_online' => true,
            'last_online_at' => now(),
        ]);
    }

    public function goOffline()
    {
        $this->update([
            'is_online' => false,
            'is_available' => false,
        ]);
    }

    public function setAvailable(bool $available = true)
    {
        $this->update(['is_available' => $available]);
    }

    public function calculateRating()
    {
        // Calculate rating based on completed deliveries
        // This is a placeholder - implement actual rating calculation logic
        $completed = $this->completed_deliveries;
        $total = $this->total_deliveries;

        if ($total > 0) {
            $this->rating = ($completed / $total) * 5; // Simple calculation
            $this->save();
        }
    }

    public function updateCompletionRate()
    {
        $total = $this->total_deliveries;
        if ($total > 0) {
            $this->completion_rate = ($this->completed_deliveries / $total) * 100;
            $this->save();
        }
    }

    public function incrementDeliveryCount(bool $completed = true)
    {
        $this->increment('total_deliveries');
        if ($completed) {
            $this->increment('completed_deliveries');
        } else {
            $this->increment('cancelled_deliveries');
        }
        $this->updateCompletionRate();
        $this->calculateRating();
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isVerified()
    {
        return $this->is_verified;
    }

    public function isAccredited()
    {
        return $this->is_accredited;
    }

    public function isOnline()
    {
        return $this->is_online;
    }

    public function isAvailable()
    {
        return $this->is_available;
    }
}
