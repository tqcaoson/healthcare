<?php

namespace App\Domain\Audit\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'event_type',
        'event_at',
        'user_id',
        'user_name',
        'user_role',
        'session_id',
        'ip_address',
        'user_agent',
        'resource_type',
        'resource_id',
        'patient_id',
        'action',
        'old_values',
        'new_values',
        'request_path',
        'request_method',
        'correlation_id',
        'log_hash',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}