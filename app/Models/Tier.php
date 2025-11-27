<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\JsonCast;
use Illuminate\Support\Carbon;

class Tier extends Model {
    use HasFactory, SoftDeletes;

    const RETAIL_TIERS   = "retails";
    const SUPPLIER_TIERS = "suppliers";

    const tier_types = [

        self::RETAIL_TIERS,
        self::SUPPLIER_TIERS,

    ];

    const HOURLYBILLINGDURATION   = 'Every 1 hr';
    const SIXHOURBILLINGDURATION  = 'Every 6 hrs';
    const DAILYBILLINGDURATION    = 'day';
    const WEEKLYBILLINGDURATION   = 'week';
    const MONTHLYBILLINGDURATION  = "month";
    const SIXMONTHBILLINGDURATION = "6 months";
    const YEARLYBILLINGDUARTION   = "year";

    const BILLINGDURATIONS = [
        self::HOURLYBILLINGDURATION,
        self::SIXHOURBILLINGDURATION,
        self::DAILYBILLINGDURATION,
        self::WEEKLYBILLINGDURATION,
        self::MONTHLYBILLINGDURATION,
        self::SIXMONTHBILLINGDURATION,
        self::YEARLYBILLINGDUARTION,
    ];

    protected $guarded = [];

    protected $casts = [
        'benefits' => JsonCast::class,
        'trial_price' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function tierable()
    {
        return $this->morphTo();
    }

    public function tierItems()
    {
        return $this->hasMany(TierItem::class, 'tier_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tier_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'permission_tier');
    }

    /**
     * Check if tier has trial period
     */
    public function hasTrial(): bool
    {
        return $this->trial_period_days > 0;
    }

    /**
     * Get trial end date for new subscription
     */
    public function getTrialEndDate(): ?Carbon
    {
        return $this->hasTrial() ? now()->addDays($this->trial_period_days) : null;
    }

    /**
     * Get billing interval in days
     */
    public function getBillingIntervalDays(): int
    {
        switch ($this->billing_duration) {
            case self::HOURLYBILLINGDURATION:
                return 0; // Hourly billing
            case self::SIXHOURBILLINGDURATION:
                return 0; // 6-hour billing
            case self::DAILYBILLINGDURATION:
                return 1;
            case self::WEEKLYBILLINGDURATION:
                return 7;
            case self::MONTHLYBILLINGDURATION:
                return 30; // Approximate
            case self::SIXMONTHBILLINGDURATION:
                return 180; // Approximate
            case self::YEARLYBILLINGDUARTION:
                return 365; // Approximate
            default:
                return 30; // Default to monthly
        }
    }

    /**
     * Get formatted billing duration for display
     */
    public function getFormattedBillingDuration(): string
    {
        return match($this->billing_duration) {
            self::HOURLYBILLINGDURATION => 'per hour',
            self::SIXHOURBILLINGDURATION => 'every 6 hours',
            self::DAILYBILLINGDURATION => 'daily',
            self::WEEKLYBILLINGDURATION => 'weekly',
            self::MONTHLYBILLINGDURATION => 'monthly',
            self::SIXMONTHBILLINGDURATION => 'every 6 months',
            self::YEARLYBILLINGDUARTION => 'yearly',
            default => 'monthly'
        };
    }

    /**
     * Scope for active tiers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for recommended tiers
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

}
