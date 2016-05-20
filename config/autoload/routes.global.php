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
            'middleware' => 'App\Action\WelcomeAction',
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'login',
            'path' => '/login',
            'middleware' => 'App\Action\Auth\LoginAction',
            'allowed_methods' => ['GET', 'POST']
        ]
    ],
];
