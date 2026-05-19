<?php

namespace App\Notifications;

use App\Domain\Scheduling\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {
    }

    public function via(
        object $notifiable
    ): array {

        return ['mail'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {

        return (new MailMessage)
            ->subject('Appointment Reminder')
            ->line(
                'You have an upcoming appointment.'
            );
    }
}