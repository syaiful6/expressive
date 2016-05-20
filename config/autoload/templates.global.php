<?php

return [
    'dependencies' => [
        'factories' => [
            //'Zend\Expressive\FinalHandler' =>
            //    Zend\Expressive\Container\TemplatedErrorHandlerFactory::class,

            Zend\Expressive\Template\TemplateRendererInterface::class =>
                Zend\Expressive\Twig\TwigRendererFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'twig.html',
        'paths'     => [
            'app'    => ['templates/app'],
            'layout' => ['templates/layout'],
            'error'  => ['templates/error'],
        ],
        'context_processors' => [
            'App\Foundation\ContextProcessor\UserContext',
            'App\Foundation\ContextProcessor\CsrfContexProcessor',
            'App\Flash\FlashContextProcessor',
        ],
    ],

    'twig' => [
        'cache_dir'      => 'data/cache/twig',
        'assets_url'     => '/',
        'assets_version' => null,
        'extensions'     => [
            // extension service names or instances
        ],
    ],
];
