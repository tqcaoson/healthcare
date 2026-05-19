<?php

return [

    'driver' => env(
        'SCOUT_DRIVER',
        'meilisearch'
    ),

    'queue' => true,

    'after_commit' => true,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'meilisearch' => [
        'host' => env(
            'MEILISEARCH_HOST',
            'http://meilisearch:7700'
        ),
        'key' => env(
            'MEILISEARCH_KEY'
        ),
    ],
];