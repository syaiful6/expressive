<?php

return [
    'session' => [
        'backend' => 'file',

        'lifetime' => 120,

        'expire_on_close' => false,

        'cookie' => 'petsitter_cookie',

        'lottery' => [2, 100],

        'path' => '/',

        'domain' => null,

        'secure' => false,

        'httponly' => false,
    ]
];
