<?php

namespace App\Notifications;

use App\Domain\Result\Models\LabResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalLabNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LabResult $result
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {

        return (new MailMessage)
            ->subject('Critical Lab Result')
            ->line(
                'A critical lab result requires review.'
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'result_id' => $this->result->id,
            'critical' => true,
        ];
    }
}