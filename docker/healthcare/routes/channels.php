<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'patient.{patientId}',
    \App\Broadcasting\PatientChannel::class
);