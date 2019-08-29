<?php
return [
    'api_key' => [
        'name' => env("API_KEY_NAME", 'X-Authorization')
    ],
    'is_log_access_event' => env("IS_LOG_ACCESS_EVENT", false),
    'cache' => [
        'tag' => env("API_KEY_CACHE_TAG", 'x_api_key')
    ]
];