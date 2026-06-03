# config/health.php

```php
return [

    'critical_value_acknowledge_minutes' => env('CRITICAL_ACK_MINUTES', 30),

    'alert_fatigue' => [
        'max_alerts_per_session'  => 15,
        'override_rate_threshold' => 0.90,  // 90% override → flag for review
        'alert_cooldown_minutes'  => 60,    // Không hiện lại cùng alert trong 60 phút
    ],

    'medication' => [
        'administration_window_minutes' => 30,  // ±30 phút so với giờ scheduled
        'high_alert_drugs_require_scan' => true,
        'high_alert_rxnorm_codes'       => [
            '5489',    // Hydrocodone
            '7052',    // Morphine
            '56795',   // Insulin
            '11289',   // Warfarin
            '3498',    // Digoxin
        ],
    ],

    'order' => [
        'stat_turnaround_minutes'  => 60,   // TAT target cho STAT orders
        'routine_turnaround_hours' => 24,
    ],

    'session' => [
        'idle_timeout_minutes'    => env('SESSION_LIFETIME', 15),
        'max_concurrent_sessions' => 3,
        'force_logout_on_breach'  => true,
    ],

    'password' => [
        'min_length'              => 12,
        'require_uppercase'       => true,
        'require_number'          => true,
        'require_special'         => true,
        'expiry_days'             => env('PASSWORD_EXPIRY_DAYS', 90),
        'history_count'           => 12,   // Không được dùng lại 12 passwords gần nhất
        'failed_attempts_lockout' => 5,
        'lockout_minutes'         => 30,
    ],

    'mfa' => [
        'required'          => env('MFA_REQUIRED', true),
        'totp_window'       => 1,    // ±1 TOTP period tolerance
        'backup_codes_count'=> 8,
    ],

    'audit' => [
        'retention_years'         => 6,    // HIPAA minimum 6 năm
        'archive_after_years'     => 2,    // Archive sang cold storage sau 2 năm
        'phi_fields_to_never_log' => [     // Không bao giờ xuất hiện trong log
            'password', 'mfa_secret', 'patient_token',
            'phone', 'email', 'address_line',
        ],
    ],

    'terminology' => [
        'loinc_version'  => '2.76',
        'snomed_version' => '2024-03-01',
        'icd10_version'  => '2024',
        'rxnorm_version' => 'current',
        'cache_ttl_hours'=> 24,
    ],

    'downtime' => [
        'cds_timeout_ms'           => 500,   // Fail-open sau 500ms
        'lis_timeout_seconds'      => 10,
        'ris_timeout_seconds'      => 10,
        'pharmacy_timeout_seconds' => 10,
        'queue_max_size_warning'   => 500,   // Alert khi queue > 500
    ],

    // Các fields nhạy cảm encrypt trong DB
    'encrypted_fields' => [
        'patient_demographics' => ['phone', 'email', 'address_line'],
        'patient_identifiers'  => ['value'],  // CCCD, BHYT, passport
        'users'                => ['mfa_secret'],
    ],
];
```
