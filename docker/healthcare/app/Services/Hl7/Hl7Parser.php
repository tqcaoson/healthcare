<?php

namespace App\Services\Hl7;

class Hl7Parser
{
    public function parse(string $message): array
    {
        $segments = explode("\r", $message);

        $parsed = [];

        foreach ($segments as $segment) {

            $fields = explode('|', $segment);

            $parsed[$fields[0]][] = $fields;
        }

        return $parsed;
    }
}