<?php

return [

    'defaults' => [

        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => [
                'default',
                'critical',
                'hl7',
                'notifications',
            ],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 10,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 512,
            'tries' => 3,
            'timeout' => 120,
        ],
    ],

    'environments' => [

        'production' => [
            'supervisor-1' => [
                'maxProcesses' => 50,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 5,
            ],
        ],
    ],
];