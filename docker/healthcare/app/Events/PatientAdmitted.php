<?php

namespace App\Events;

use App\Domain\Encounter\Models\Encounter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientAdmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Encounter $encounter
    ) {
    }
}
