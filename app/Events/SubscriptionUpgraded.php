<?php

namespace App\Events;

use App\Models\Subscription;
use App\Models\Tier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SubscriptionUpgraded
{
    use Dispatchable, SerializesModels;

    public $subscription;
    public $oldTier;
    public $newTier;
    public $upgradeDate;
    public $proratedAmount;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, Tier $oldTier, Tier $newTier, Carbon $upgradeDate, ?float $proratedAmount = null)
    {
        $this->subscription = $subscription;
        $this->oldTier = $oldTier;
        $this->newTier = $newTier;
        $this->upgradeDate = $upgradeDate;
        $this->proratedAmount = $proratedAmount;
    }
}
