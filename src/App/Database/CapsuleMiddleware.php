<?php

namespace App\Database;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CapsuleMiddleware
{

    protected $configs;

    /**
     *
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {

        EloquentBooter::boot($this->configs);
        return $next($request, $response);
    }
}
