<?php

namespace App\Foundation\ContextProcessor;

use App\Middleware\Csrf;
use Psr\Http\Message\ServerRequestInterface;
use App\Functional\LazyString;

class CsrfContexProcessor
{
    /**
     *
     */
    public function __invoke(ServerRequestInterface $request)
    {
        // delay the call to Csrf::token, chances are the template may not used
        // at all, eg there are no form. So it the middleware not patch vary header
        // each request.
        $token = function () use ($request) {
            $token = Csrf::getToken($request);
            if (!$token) {
                $token = 'NOTPROVIDED'; // for debugging
            }
            return $token;
        };
        return ['csrftoken' => new LazyString($token)];
    }
}
