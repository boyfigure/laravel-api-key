<?php
return [
    'api_key' => [
        'name' => env("API_KEY_NAME", 'X-Authorization')
    ],
    'enable_log_access_event' => env("ENABLE_LOG_ACCESS_EVENT", false),
    'cache' => [
        'tag' => env("API_KEY_CACHE_TAG", 'x_api_key'),
        'active' => env("API_KEY_CACHE_ACTIVE", false),
    ]
];