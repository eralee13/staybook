<?php

return [
    'enabled' => env('SCRAMBLE_ENABLED', true),

    'info' => [
        'title' => 'StayBook Partner API',
        'version' => 'v1',
        'description' => 'Документация партнёрского API (X-API-Key)',
    ],

    'servers' => [
        rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api',
    ],

    'routes' => [
        'include' => ['api/*', 'api/v1/*'],
        'exclude' => [],
    ],

    'generate_extra_info' => true,

    // 🔒 схема безопасности
    'securitySchemes' => [
        'ApiKeyAuth' => [
            'type' => 'apiKey',
            'in'   => 'header',
            'name' => 'X-API-Key',
        ],
    ],

    // применяем ко всем маршрутам
    'security' => [
        ['ApiKeyAuth' => []],
    ],

    'ui' => [
        'path' => 'docs/api',
    ],
    'openapi' => [
        'path' => 'docs/api.json',
    ],
];