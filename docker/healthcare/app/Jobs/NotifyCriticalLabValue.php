<?php

namespace App\Jobs;

use App\Domain\Result\Models\LabResult;
use App\Notifications\CriticalLabNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotifyCriticalLabValue implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 60;

    public function __construct(
        public LabResult $result
    ) {
        // Use the critical queue so this is processed before routine jobs
        $this->onQueue('critical');
    }

    public function handle(): void
    {
        $result = $this->result->loadMissing(['patient.currentDemographics']);

        $patient = $result->patient;

        if (! $patient) {
            Log::error('NotifyCriticalLabValue: no patient for result', [
                'result_id' => $result->id,
            ]);
            return;
        }

        // Notify the ordering provider (if resolvable) and any on-call user
        // who can receive notifications. In production this list comes from
        // a roster/on-call service; for now we notify all clinical-role users.
        $recipients = \App\Models\User::query()
            ->whereIn('role', ['physician', 'nurse', 'lab_director'])
            ->get();

        foreach ($recipients as $user) {
            $user->notify(new CriticalLabNotification($result));
        }

        Log::channel('audit')->info('critical_lab_notified', [
            'result_id'    => $result->id,
            'patient_id'   => $patient->id,
            'loinc_code'   => $result->loinc_code,
            'recipients'   => $recipients->pluck('id'),
            'notified_at'  => now()->toIso8601String(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('NotifyCriticalLabValue failed — manual escalation required', [
            'result_id' => $this->result->id,
            'error'     => $e->getMessage(),
        ]);
    }
}