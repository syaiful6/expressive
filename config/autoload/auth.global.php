<?php

return [
    'dependencies' => [
        'invokables' => [
            'App\Auth\AuthSignals' => 'App\Auth\AuthSignals'
        ],
        'factories' => [
            'App\Auth\ModelBackend' => 'App\Auth\AuthServiceFactory',
            'App\Auth\Authenticator' => 'App\Auth\AuthServiceFactory',
            'App\Auth\AuthenticationMiddleware' => 'App\Auth\AuthServiceFactory',
            'App\Auth\Passwords\TokenRepositoryInterface'
                => 'App\Auth\Password\PasswordResetServiceFactory',
            'Illuminate\Contracts\Auth\PasswordBroker'
                => 'App\Auth\Password\PasswordResetServiceFactory',
        ]
    ],
    'auth' => [
        'model' => 'App\Auth\User',
        'backends' => [
            'App\Auth\ModelBackend'
        ],
        'passwords' => [
            'user' => [
                'table' => 'password_resets',
                'template' => 'app::auth/password',
                'expire' => 60
            ]
        ]
    ]
];
