<?php

namespace App\Domain\Audit\Services;

use App\Domain\Audit\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService
{
    public function log(string $eventType, array $context = []): void
    {
        $entry = array_merge([
            'event_type' => $eventType,
            'event_at' => now(),
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->username,
            'user_role' => Auth::user()?->role,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_path' => request()->path(),
            'request_method' => request()->method(),
            'correlation_id' => request()->header(
                'X-Correlation-ID',
                (string) Str::uuid()
            ),
        ], $context);

        $entry['log_hash'] = $this->computeHash($entry);

        AuditLog::create($entry);
    }

    private function computeHash(array $entry): string
    {
        // Lock the latest row to prevent concurrent requests from forking
        // the hash chain and breaking tamper-evident integrity checks.
        $lastHash = AuditLog::lockForUpdate()
            ->latest('id')
            ->value('log_hash') ?? '';

        return hash(
            'sha256',
            $lastHash . json_encode($entry)
        );
    }
}