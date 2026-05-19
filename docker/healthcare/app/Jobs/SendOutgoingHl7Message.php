<?php

namespace App\Jobs;

use App\Services\Hl7\MllpClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOutgoingHl7Message implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $payload
    ) {
    }

    public function handle(MllpClient $client): void
    {
        $client->send($this->payload);
    }
}