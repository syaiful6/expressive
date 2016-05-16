<?php

namespace App\Foundation\ContextProcessor;

use App\Middleware\Csrf;
use Psr\Http\Message\ServerRequestInterface;

class CsrfContexProcessor
{
    /**
     *
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $token = Csrf::getToken($request);
        if (!$token) {
            $token = 'NOTPROVIDED'; // for debugging
        }
        return ['csrftoken' => $token];
    }
}
