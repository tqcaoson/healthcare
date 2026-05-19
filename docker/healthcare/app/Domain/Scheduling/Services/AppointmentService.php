<?php

namespace App\Domain\Scheduling\Services;

use App\Domain\Scheduling\Models\Appointment;
use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;

class AppointmentService
{
    public function book(array $payload): Appointment
    {
        $appointment = Appointment::create([
            'uuid' => (string) str()->uuid(),
            ...$payload,
            'status' => 'booked',
        ]);

        AppointmentBooked::dispatch($appointment);

        return $appointment;
    }

    public function cancel(
        Appointment $appointment,
        string $reason
    ): void {

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        AppointmentCancelled::dispatch($appointment);
    }
}