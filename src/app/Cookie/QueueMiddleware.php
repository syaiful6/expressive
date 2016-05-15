<?php

namespace App\Cookie;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class QueueMiddleware
{
    protected $cookieJar;

    /**
     *
     */
    public function __construct(QueueingCookieFactory $cookieJar)
    {
        $this->cookieJar = $cookieJar;
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $response = $next($request, $response);

        if ($response->hasHeader('Set-Cookie')) {
            $cookies = $response->getHeader('Set-Cookie');
        } else {
            $cookies = [];
        }

        $cookies = array_merge($cookies, $this->cookieJar->getQueuedCookies());

        return $response->withHeader('Set-Cookie', $cookies);
    }
}
