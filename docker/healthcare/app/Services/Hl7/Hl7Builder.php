<?php

namespace App\Services\Hl7;

class Hl7Builder
{
    public function buildOrm(array $payload): string
    {
        return implode("\r", [
            'MSH|^~\&|HIS|HOSPITAL|LIS|LAB|' . now()->format('YmdHis'),
            'PID|' . $payload['mrn'],
            'ORC|NW|' . $payload['order_number'],
        ]);
    }
}