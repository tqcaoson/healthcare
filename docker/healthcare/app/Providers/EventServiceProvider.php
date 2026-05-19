<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\PhiAccessed;
use App\Listeners\LogPhiAccess;
use App\Listeners\RouteOrderToSubsystem;
use App\Listeners\TriggerCdsAlert;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PhiAccessed::class => [
            LogPhiAccess::class,
        ],

        OrderPlaced::class => [
            TriggerCdsAlert::class,
            RouteOrderToSubsystem::class,
        ],

        \App\Events\OrderPlaced::class => [
            \App\Listeners\RouteOrderToSubsystem::class,
            \App\Listeners\TriggerCdsAlert::class,
        ],
    
        \App\Events\MedicationAdministered::class => [
            \App\Listeners\DeductMedicationInventory::class,
        ],
    
        \App\Events\AppointmentBooked::class => [
            \App\Listeners\SendAppointmentNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}