<?php

namespace App\Console;

use App\Console\Commands\ArchiveAuditLogs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ArchiveAuditLogs::class,
    ];

    protected function schedule(
        Schedule $schedule
    ): void {

        $schedule->command(
            'audit:archive'
        )->daily();

        $schedule->command(
            'queue:prune-failed'
        )->daily();

        $schedule->command(
            'model:prune'
        )->daily();
    }
}