<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
        // Map middleware -> factories here
        'factories' => [
            'App\Action\WelcomeView' => 'App\Action\WelcomeViewFactory'
        ],
    ],

    'routes' => [
        [
            'name'  => 'welcome',
            'path'  => '/',
            'middleware' => 'App\Action\WelcomeView',
            'allowed_methods' => ['GET', 'POST'],
        ],
    ],
];
