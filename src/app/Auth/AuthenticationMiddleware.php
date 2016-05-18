<?php

namespace App\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AuthenticationMiddleware
{
    /**
     * @var App\Auth\Authenticator
     */
    protected $authenticator;

    /**
     *
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     *
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $user = $this->authenticator->user($request);
        $request = $request->withAttribute('user', $user);

        return $next($request, $response);
    }
}
