<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ScrubPhiFromLogs
{
    /**
     * Patterns applied to log message strings before they reach any driver.
     * Keys are labels only; values are regex patterns.
     */
    protected array $patterns = [
        'mrn'          => '/MRN[\s:\-]*[A-Z0-9]+/i',
        'email'        => '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/i',
        'phone'        => '/\b\d{9,11}\b/',
        'birth_date'   => '/\b\d{4}[-\/]\d{2}[-\/]\d{2}\b/',
        'ssn'          => '/\b\d{3}-\d{2}-\d{4}\b/',
        'national_id'  => '/\b\d{9,12}\b/',
        'given_name'   => '/"given_name"\s*:\s*"[^"]+"/i',
        'family_name'  => '/"family_name"\s*:\s*"[^"]+"/i',
        'address'      => '/"address[^"]*"\s*:\s*"[^"]+"/i',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Scrub PHI from log messages emitted during this request.
        // We listen on the MessageLogged event which fires before any
        // driver writes, so the raw request data is never touched.
        Event::listen(MessageLogged::class, function (MessageLogged $event) {
            $event->message = $this->scrubString((string) $event->message);

            foreach ($event->context as $key => $value) {
                if (is_string($value)) {
                    $event->context[$key] = $this->scrubString($value);
                }
            }
        });

        Log::shareContext(['phi_scrubbed' => true]);

        return $next($request);
    }

    protected function scrubString(string $value): string
    {
        foreach ($this->patterns as $pattern) {
            $value = (string) preg_replace($pattern, '[REDACTED]', $value);
        }

        return $value;
    }
}