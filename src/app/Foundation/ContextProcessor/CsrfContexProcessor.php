<?php

namespace App\Foundation\ContextProcessor;

use Psr\Http\Message\ServerRequestInterface;
use App\Functional\LazyString;

class CsrfContexProcessor
{
    /**
     *
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $token = function () use ($request) {
            $getToken = $request->getAttribute('CSRF_TOKEN_GET');
            $token = $getToken($request);
            if (!$token) {
                $token = 'NOTPROVIDED'; // for debugging
            }
            return $token;
        };
        return ['csrftoken' => new LazyString($token)];
    }
}
