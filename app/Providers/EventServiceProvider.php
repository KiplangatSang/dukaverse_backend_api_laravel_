<?php

namespace App\Providers;

use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionReactivated;
use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionUpgraded;
use App\Listeners\SendSubscriptionNotification;
use App\Listeners\SubscriptionCancelledListener;
use App\Listeners\SubscriptionExpiredListener;
use App\Listeners\SubscriptionReactivatedListener;
use App\Listeners\SubscriptionRenewedListener;
use App\Listeners\SubscriptionUpgradedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SubscriptionCreated::class => [
            SendSubscriptionNotification::class,
        ],
        SubscriptionCancelled::class => [
            SubscriptionCancelledListener::class,
        ],
        SubscriptionExpired::class => [
            SubscriptionExpiredListener::class,
        ],
        SubscriptionReactivated::class => [
            SubscriptionReactivatedListener::class,
        ],
        SubscriptionRenewed::class => [
            SubscriptionRenewedListener::class,
        ],
        SubscriptionUpgraded::class => [
            SubscriptionUpgradedListener::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
