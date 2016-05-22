<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
        // Map middleware -> factories here
        'factories' => [

        ],
    ],

    'routes' => [
        [
            'name'  => 'welcome',
            'path'  => '/',
            'middleware' => 'Petsitter\Http\Actions\WelcomeAction',
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'login',
            'path' => '/login',
            'middleware' => 'Petsitter\Http\Actions\Auth\LoginAction',
            'allowed_methods' => ['GET', 'POST']
        ],
        [
            'name' => 'logout',
            'path' => '/logout',
            'middleware' => 'App\Foundation\Auth\LogoutAction',
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'register',
            'path' => '/register',
            'middleware' => 'Petsitter\Http\Actions\Auth\RegisterAction',
            'allowed_methods' => ['GET', 'POST']
        ],
        [
            'name' => 'reset_password',
            'path' => '/password/reset',
            'middleware' => 'Petsitter\Http\Actions\Auth\ResetsPasswords',
            'allowed_methods' => ['GET', 'POST']
        ],
    ],
];
