<?php

namespace Petsitter\Http\Middleware;

use App\Foundation\Http\WebMiddlewareFactory as BaseFactory;

class WebMiddlewareFactory extends BaseFactory
{
    /**
     * Add your middleware here to run them on web. So we can differentiate it
     * with api.
     *
     * @var array
     */
    protected $webStack = [
        'App\Cookie\QueueMiddleware',
        'App\Session\SessionMiddleware',
        'App\Middleware\Csrf',
        'App\Auth\AuthenticationMiddleware',
        'App\Flash\FlashMessageMiddleware',
        'App\Middleware\ContextProcessor',
    ];
}
