<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled
{
    use Dispatchable, SerializesModels;

    public $subscription;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription, ?string $reason = null)
    {
        $this->subscription = $subscription;
        $this->reason = $reason;
    }
}
