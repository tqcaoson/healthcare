<?php

namespace App\Services\Observability;

use Illuminate\Support\Str;

class CorrelationIdService
{
    public function current(): string
    {
        return request()->header(
            'X-Correlation-ID',
            (string) Str::uuid()
        );
    }
}