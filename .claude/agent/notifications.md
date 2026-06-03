# Notification System

## Notification Classes

```php
// Kết quả lab nguy kịch — gửi ngay, không queue delay
class CriticalLabValueNotification extends Notification implements ShouldQueue
{
    public string $queue = 'critical';
    public int $tries    = 5;
    public int $timeout  = 30;

    public function __construct(private LabResult $result) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Luôn gửi in-app + email cho critical values
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'critical_lab',
            'title'         => '⚠️ Kết quả nguy kịch cần xử lý',
            'message'       => "{$this->result->loinc_display}: {$this->result->value_quantity} {$this->result->value_unit} [{$this->result->abnormal_flag}]",
            'lab_result_id' => $this->result->id,
            'patient_id'    => $this->result->patient_id,
            'patient_name'  => $this->result->patient->currentDemographics->family_name,
            'action_url'    => "/patients/{$this->result->patient->uuid}/lab-results/{$this->result->id}",
            'requires_acknowledgment' => true,
            'acknowledge_by' => now()->addMinutes(30)->toIso8601String(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[URGENT] Kết quả nguy kịch — {$this->result->patient->mrn}")
            ->line("**{$this->result->loinc_display}**: {$this->result->value_quantity} {$this->result->value_unit}")
            ->line("**Flag**: {$this->result->abnormal_flag}")
            ->action('Xem ngay', url("/patients/{$this->result->patient->uuid}/lab-results/{$this->result->id}"))
            ->line('Vui lòng acknowledge trong vòng 30 phút.');
    }
}

// Nhắc lịch hẹn — gửi trước 24h và 1h
class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(
        private Appointment $appointment,
        private string $reminderType  // '24h' | '1h'
    ) {}

    public function via(object $notifiable): array
    {
        $prefs    = $notifiable->notificationPreference('appointment_reminder');
        $channels = ['database'];
        if ($prefs?->channel_email) $channels[] = 'mail';
        if ($prefs?->channel_sms)   $channels[] = 'vonage';
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'reminder_type'  => $this->reminderType,
            'message'        => "Lịch hẹn của bạn với {$this->appointment->provider->username} vào lúc {$this->appointment->start_at->format('H:i d/m/Y')}",
        ];
    }
}

// Order mới cần dược sĩ verify
class NewMedicationOrderNotification extends Notification implements ShouldQueue
{
    public string $queue = 'clinical';

    public function __construct(private MedicationOrder $order) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'new_medication_order',
            'order_id'   => $this->order->order_id,
            'drug_name'  => $this->order->drug_name,
            'patient_id' => $this->order->order->patient_id,
            'priority'   => $this->order->order->priority,
            'action_url' => "/pharmacy/orders/{$this->order->order_id}",
        ];
    }
}

// Kết quả lab đã có — cho ordering provider
class LabResultAvailableNotification extends Notification implements ShouldQueue
{
    public string $queue = 'clinical';

    public function __construct(private LabResult $result) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'lab_result_available',
            'lab_result_id' => $this->result->id,
            'loinc_display' => $this->result->loinc_display,
            'is_abnormal'   => $this->result->abnormal_flag !== 'N',
            'patient_id'    => $this->result->patient_id,
        ];
    }
}
```
