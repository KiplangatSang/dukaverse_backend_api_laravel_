<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SubscriptionRenewed
{
    use Dispatchable, SerializesModels;

    public $subscription;
    public $renewalDate;
    public $nextBillingDate;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, Carbon $renewalDate, Carbon $nextBillingDate)
    {
        $this->subscription = $subscription;
        $this->renewalDate = $renewalDate;
        $this->nextBillingDate = $nextBillingDate;
    }
}
