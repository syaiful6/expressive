<?php

return [
    'debug' => false,

    'config_cache_enabled' => false,

    'timezone' => 'Asia/Jakarta',

    'zend-expressive' => [
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
