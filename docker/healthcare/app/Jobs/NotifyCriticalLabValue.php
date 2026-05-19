<?php

namespace App\Jobs;

use App\Domain\Result\Models\LabResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyCriticalLabValue implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LabResult $result
    ) {
    }

    public function handle(): void
    {
        //
    }
}