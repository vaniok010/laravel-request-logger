<?php

declare(strict_types=1);

return [
    'middleware' => [],
    'store' => Hryha\RequestLogger\Stores\Database::class,
    'formatter' => Hryha\RequestLogger\Formatters\JsonFormatter::class,
    'timezone' => env('REQUEST_LOGGER_TIMEZONE', env('APP_TIMEZONE', config('app.timezone', 'UTC'))),
    'date_format' => env('REQUEST_LOGGER_DATE_FORMAT', 'Y-m-d'),
    'time_format' => env('REQUEST_LOGGER_TIME_FORMAT', 'H:i:s.u'),
    'logs_per_page' => (int)env('REQUEST_LOGGER_LOGS_PER_PAGE', 50),
    'log_keep_days' => (int)env('REQUEST_LOGGER_KEEP_DAYS', 14),
    'enabled' => env('REQUEST_LOGGER_ENABLED', true),
    'dark_mode' => env('REQUEST_LOGGER_DARK_MODE', false),
    'table_name' => 'request_logs',
    'custom_fields' => explode(',', env('REQUEST_LOGGER_CUSTOM_FIELDS', '')),
    'ignore_paths' => explode(',', env('REQUEST_LOGGER_IGNORE_PATHS', 'request-logs*,telescope*,horizon*,nova-api*')),
    'hide' => [
        'mask' => env('REQUEST_LOGGER_HIDE_MASK', '|^_-|'),
        'request' => [
            'headers' => explode(',', env('REQUEST_LOGGER_HIDE_REQUEST_HEADERS', 'authorization,proxy-authorization,cookie,x-api-key,php-auth-user,php-auth-pw')),
            'content' => explode(',', env('REQUEST_LOGGER_HIDE_REQUEST_CONTENT', 'password,old_password,new_password,token,access_token,refresh_token')),
        ],
        'response' => [
            'headers' => explode(',', env('REQUEST_LOGGER_HIDE_RESPONSE_HEADERS', 'set-cookie,www-authenticate,server,x-powered-by,via,referrer-policy,access-control-allow-origin')),
            'content' => explode(',', env('REQUEST_LOGGER_HIDE_RESPONSE_HEADERS', 'password,token,access_token,refresh_token')),
        ],
    ],
    'sampling' => [
        'enabled' => env('REQUEST_LOGGER_SAMPLING_ENABLED', false),
        'rates' => [
            '2xx' => round((float)env('REQUEST_LOGGER_SAMPLING_2XX', 100.0), 2),
            '3xx' => round((float)env('REQUEST_LOGGER_SAMPLING_3XX', 100.0), 2),
            '4xx' => round((float)env('REQUEST_LOGGER_SAMPLING_4XX', 100.0), 2),
            '5xx' => round((float)env('REQUEST_LOGGER_SAMPLING_5XX', 100.0), 2),
        ],
        'always_log_slow_requests' => (int)env('REQUEST_LOGGER_ALWAYS_LOG_SLOW', 0),
        'always_log_heavy_memory' => (int)env('REQUEST_LOGGER_ALWAYS_LOG_HEAVY_MEMORY', 0),
    ],
];
