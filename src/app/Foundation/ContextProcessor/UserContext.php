<?php

namespace App\Foundation\ContextProcessor;

use Psr\Http\Message\ServerRequestInterface;

class UserContext
{
    /**
     *
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return ['user' => $request->getAttribute('user')];
    }
}