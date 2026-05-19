<?php

namespace App\Console\Commands;

use App\Domain\Audit\Models\AuditLog;
use Illuminate\Console\Command;

class ArchiveAuditLogs extends Command
{
    protected $signature = 'audit:archive';

    protected $description = 'Archive old audit logs';

    public function handle(): int
    {
        AuditLog::query()
            ->where(
                'event_at',
                '<',
                now()->subYears(
                    config(
                        'health.audit_retention_years'
                    )
                )
            )
            ->delete();

        $this->info('Archived.');

        return self::SUCCESS;
    }
}