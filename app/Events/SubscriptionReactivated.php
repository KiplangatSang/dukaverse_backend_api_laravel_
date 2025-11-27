<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReactivated
{
    use Dispatchable, SerializesModels;

    public $subscription;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
