<?php

namespace App\Providers;

use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;
use App\Events\MedicationAdministered;
use App\Events\OrderPlaced;
use App\Events\PatientAdmitted;
use App\Events\PatientDischarged;
use App\Events\PhiAccessed;
use App\Listeners\DeductMedicationInventory;
use App\Listeners\LogPhiAccess;
use App\Listeners\RouteOrderToSubsystem;
use App\Listeners\SendAppointmentNotification;
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

        MedicationAdministered::class => [
            DeductMedicationInventory::class,
        ],

        AppointmentBooked::class => [
            SendAppointmentNotification::class,
        ],

        PatientAdmitted::class => [],

        PatientDischarged::class => [],

        AppointmentCancelled::class => [],
    ];

    public function boot(): void
    {
        //
    }
}
