<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIncomingHl7Message implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $payload
    ) {
    }

    public function handle(): void
    {
        //
    }
}