<?php

namespace App\Events;

use App\Models\Delivery;
use App\Models\DeliveryPerson;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiderAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $delivery;
    public $rider;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, DeliveryPerson $rider)
    {
        $this->delivery = $delivery;
        $this->rider = $rider;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('rider-assigned');
    }
}
