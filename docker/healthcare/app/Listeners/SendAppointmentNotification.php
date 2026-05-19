<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use Illuminate\Support\Facades\Notification;

class SendAppointmentNotification
{
    public function handle(
        AppointmentBooked $event
    ): void {

        Notification::route(
            'mail',
            'patient@example.com'
        )->notify(
            new \App\Notifications\AppointmentReminderNotification(
                $event->appointment
            )
        );
    }
}