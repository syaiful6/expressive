<?php

return [
    'session' => [
        'backend' => 'file',
        // session lifetime in seconds, default 2 weeks
        'lifetime' => 60 * 60 * 24 * 7 * 2,

        'expire_on_close' => false,

        'cookie' => 'petsitter_cookie',

        'lottery' => [2, 100],

        'path' => '/',

        'domain' => null,

        'secure' => false,

        'httponly' => false,
    ]
];
