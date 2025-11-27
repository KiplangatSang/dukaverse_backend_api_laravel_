<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'applicable_tiers' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'coupon_id');
    }

    /**
     * Check if coupon is valid for use
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be applied to a specific tier
     */
    public function isApplicableToTier(int $tierId): bool
    {
        if (!$this->applicable_tiers) {
            return true; // No restrictions
        }

        return in_array($tierId, $this->applicable_tiers);
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $amount * ($this->discount_value / 100);

            if ($this->maximum_discount && $discount > $this->maximum_discount) {
                $discount = $this->maximum_discount;
            }

            return $discount;
        }

        return min($this->discount_value, $amount);
    }

    /**
     * Check if minimum amount requirement is met
     */
    public function meetsMinimumAmount(float $amount): bool
    {
        return !$this->minimum_amount || $amount >= $this->minimum_amount;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('usage_count < usage_limit');
                    });
    }
}
