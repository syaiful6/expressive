<?php

return [
    'dependencies' => [
        'invokables' => [
            'App\Auth\AuthSignals'
        ],
        'factories' => [
            'App\Auth\ModelBackend' => 'App\Auth\AuthServiceFactory',
            'App\Auth\Authenticator' => 'App\Auth\AuthServiceFactory',
            'App\Auth\AuthenticationMiddleware' => 'App\Auth\AuthServiceFactory',
        ]
    ],
    'auth' => [
        'model' => 'App\Auth\User',
        'backends' => [
            'App\Auth\ModelBackend'
        ]
    ]
];
