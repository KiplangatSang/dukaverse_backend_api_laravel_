<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class Subscription extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'trial_end_date' => 'datetime',
        'expires_at' => 'datetime',
        'paid_amount' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    // Status constants
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_GRACE_PERIOD = 'grace_period';

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscriptionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function terminals(): MorphMany
    {
        return $this->morphMany(Terminal::class, 'subscribable');
    }

    public function retails(): BelongsTo
    {
        return $this->belongsTo(Retail::class, 'retail_id');
    }

    /**
     * Get the current status of the subscription
     */
    public function getStatusAttribute(): string
    {
        if ($this->isInTrial()) {
            return self::STATUS_TRIAL;
        }

        if ($this->isInGracePeriod()) {
            return self::STATUS_GRACE_PERIOD;
        }

        if ($this->isExpired()) {
            return self::STATUS_EXPIRED;
        }

        if (!$this->is_active) {
            return self::STATUS_CANCELLED;
        }

        return self::STATUS_ACTIVE;
    }

    /**
     * Check if subscription is in trial period
     */
    public function isInTrial(): bool
    {
        return $this->trial_end_date && now()->lt($this->trial_end_date);
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->gt($this->expires_at->addDays($this->grace_period_days));
    }

    /**
     * Check if subscription is in grace period
     */
    public function isInGracePeriod(): bool
    {
        if (!$this->expires_at || !$this->is_active) {
            return false;
        }

        $graceEnd = $this->expires_at->addDays($this->grace_period_days);
        return now()->gt($this->expires_at) && now()->lte($graceEnd);
    }

    /**
     * Check if subscription can be renewed
     */
    public function canBeRenewed(): bool
    {
        return $this->is_renewable && $this->auto_renewal && !$this->isExpired();
    }

    /**
     * Calculate next billing date
     */
    public function getNextBillingDate(): ?Carbon
    {
        if (!$this->tier) {
            return null;
        }

        $billingDuration = $this->tier->billing_duration;

        switch ($billingDuration) {
            case Tier::MONTHLYBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addMonth() : now()->addMonth();
            case Tier::QUARTERLYBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addMonths(3) : now()->addMonths(3);
            case Tier::YEARLYBILLINGDUARTION:
                return $this->expires_at ? $this->expires_at->addYear() : now()->addYear();
            case Tier::WEEKLYBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addWeek() : now()->addWeek();
            case Tier::DAILYBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addDay() : now()->addDay();
            case Tier::SIXHOURBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addHours(6) : now()->addHours(6);
            case Tier::HOURLYBILLINGDURATION:
                return $this->expires_at ? $this->expires_at->addHour() : now()->addHour();
            default:
                return null;
        }
    }

    /**
     * Calculate discounted price with coupon
     */
    public function calculateDiscountedPrice(): float
    {
        $basePrice = $this->subscription_price;

        if ($this->coupon && $this->coupon->isValid() && $this->coupon->isApplicableToTier($this->tier_id)) {
            $discount = $this->coupon->calculateDiscount($basePrice);
            return max(0, $basePrice - $discount);
        }

        return $basePrice;
    }

    /**
     * Extend trial period
     */
    public function extendTrial(int $days): bool
    {
        if (!$this->tier || $this->tier->max_trial_extensions <= 0) {
            return false;
        }

        $this->trial_end_date = $this->trial_end_date ? $this->trial_end_date->addDays($days) : now()->addDays($days);
        return $this->save();
    }

    /**
     * Cancel subscription
     */
    public function cancel(): bool
    {
        $this->is_active = false;
        $this->auto_renewal = false;
        return $this->save();
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(): bool
    {
        $this->is_active = true;
        $this->auto_renewal = true;
        return $this->save();
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhereRaw('expires_at + INTERVAL grace_period_days DAY > ?', [now()]);
                    });
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeInTrial($query)
    {
        return $query->whereNotNull('trial_end_date')
                    ->where('trial_end_date', '>', now());
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('expires_at')
                    ->whereRaw('expires_at + INTERVAL grace_period_days DAY < ?', [now()]);
    }

    /**
     * Scope for subscriptions expiring soon
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        $futureDate = now()->addDays($days);
        return $query->where('is_active', true)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', $futureDate)
                    ->whereRaw('expires_at + INTERVAL grace_period_days DAY > ?', [now()]);
    }

    /**
     * Scope for subscriptions with trial ending soon
     */
    public function scopeTrialEndingSoon($query, int $days = 3)
    {
        $futureDate = now()->addDays($days);
        return $query->whereNotNull('trial_end_date')
                    ->where('trial_end_date', '<=', $futureDate)
                    ->where('trial_end_date', '>', now());
    }
}
