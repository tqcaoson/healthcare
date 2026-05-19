<?php

return [

    'phi_auto_logout_minutes' => env(
        'PHI_AUTO_LOGOUT_MINUTES',
        15
    ),

    'require_mfa' => env(
        'HEALTH_REQUIRE_MFA',
        true
    ),

    'hl7_enabled' => env(
        'HL7_ENABLED',
        true
    ),

    'fhir_enabled' => env(
        'FHIR_ENABLED',
        true
    ),

    'audit_retention_years' => env(
        'AUDIT_RETENTION_YEARS',
        7
    ),

];