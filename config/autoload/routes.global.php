<?php

use App\Action;

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
        // Map middleware -> factories here
        'factories' => [
            Action\WelcomeView::class => Action\WelcomeViewFactory::class
        ],
    ],

    'routes' => [
        [
            'name'  => 'welcome',
            'path'  => '/',
            'middleware' => Action\WelcomeView::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
