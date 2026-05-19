<?php

return [

    'mllp' => [
        'host' => env('HL7_HOST'),
        'port' => env('HL7_PORT', 2575),
        'timeout' => env('HL7_TIMEOUT', 10),
    ],

    'retry_attempts' => env(
        'HL7_RETRY_ATTEMPTS',
        3
    ),

];