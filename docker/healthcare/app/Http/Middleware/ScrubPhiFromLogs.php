<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrubPhiFromLogs
{
    protected array $patterns = [
        'mrn' => '/MRN[\s:\-]*[A-Z0-9]+/i',
        'email' => '/[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i',
        'phone' => '/\b\d{9,11}\b/',
    ];

    public function handle(
        Request $request,
        Closure $next
    ) {

        foreach ($this->patterns as $pattern) {
            $request->merge(
                $this->sanitize(
                    $request->all(),
                    $pattern
                )
            );
        }

        Log::shareContext([
            'phi_scrubbed' => true,
        ]);

        return $next($request);
    }

    protected function sanitize(
        array $data,
        string $pattern
    ): array {

        array_walk_recursive($data, function (&$value) use ($pattern) {
            if (is_string($value)) {
                $value = preg_replace(
                    $pattern,
                    '[REDACTED]',
                    $value
                );
            }
        });

        return $data;
    }
}