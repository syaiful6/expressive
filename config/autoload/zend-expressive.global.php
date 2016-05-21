<?php

use function App\Foundation\env;

return [
    'debug' => env('APP_DEBUG', false),

    'config_cache_enabled' => false,
    /**
     * Application timezone and locale, here we can configure our app timezone
     */
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    'fallback_locale' => 'en',
    'lang_dir' => realpath('lang'),
    /**
     * Encryption Key set to random string with length 32 char
     */
    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],

    'cache' => [
        'default' => env('CACHE_DRIVER', 'database'),
        'prefix' => '',
        'stores' => [
            'database' => [
                'driver' => 'database',
                'table'  => 'cache',
                'connection' => null,
            ],
            'memcached' => [
                'driver'  => 'memcached',
                'servers' => [
                    [
                        'host' => '127.0.0.1', 'port' => 11211, 'weight' => 100,
                    ],
                ],
            ],
        ],
    ],

    'commands' => [
        'App\Database\Console\Commands\InstallCommand',
        'App\Database\Console\Commands\MakeMigration',
        'App\Database\Console\Commands\Migrate',
        'App\Queue\Console\Commands\FailedTable',
        'App\Queue\Console\Commands\JobTable',
        'App\Queue\Console\Commands\Listen'
    ]
];
