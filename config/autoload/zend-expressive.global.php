<?php

return [
    'debug' => false,

    'config_cache_enabled' => false,

    'timezone' => 'Asia/Jakarta',

    'locale' => 'id',

    'fallback_locale' => 'en',

    'lang_dir' => realpath('lang'),

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],

    'commands' => [
        'App\Database\Console\Commands\InstallCommand',
        'App\Database\Console\Commands\MakeMigration',
        'App\Database\Console\Commands\Migrate',
    ]
];
