<?php

return [
    'elasticsearch' => [
        'url' => env('ELASTICSEARCH_URL', 'http://localhost:9200'),
        'port' => env('ELASTICSEARCH_PORT'),
        'api_key' => env('ELASTICSEARCH_API_KEY'),
    ],
];
