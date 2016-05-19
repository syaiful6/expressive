<?php

namespace App\Foundation\Http;

use Interop\Container\ContainerInterface;

class WebMiddlewareFactory
{
    protected $webStack = [
        'App\Cookie\QueueMiddleware',
        'App\Session\SessionMiddleware',
        'App\Middleware\Csrf',
        'App\Auth\AuthenticationMiddleware',
        'App\Flash\FlashMessageMiddleware',
        'App\Middleware\ContextProcessor',
    ];

    protected $skip = '/api';

    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        $web = new SkipMiddlewarePipe($container, $this->skip);

        foreach ($this->webStack as $m) {
            $web->pipe($m);
        }

        return $web;
    }
}
