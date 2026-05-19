<?php

namespace App\Events;

use App\Domain\Clinical\Models\VitalSign;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VitalSignsUpdated implements ShouldBroadcast
{
    use InteractsWithSockets;

    public function __construct(
        public VitalSign $vital
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel(
                'patient.' . $this->vital->patient_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vitals.updated';
    }
}