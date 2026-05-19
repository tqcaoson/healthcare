<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhiAccessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        mixed $resource
    ) {
    }
}