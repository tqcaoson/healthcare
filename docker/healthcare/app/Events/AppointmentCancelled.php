<?php

namespace App\Events;

use App\Domain\Scheduling\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Appointment $appointment
    ) {
    }
}
