<?php

namespace App\Domain\Result\Services;

use App\Domain\Result\Models\LabResult;
use App\Jobs\NotifyCriticalLabValue;

class CriticalValueService
{
    public function process(LabResult $result): void
    {
        if (! $result->is_critical) {
            return;
        }

        NotifyCriticalLabValue::dispatch($result);
    }
}