<?php

namespace App\Database;

use Interop\Container\ContainerInterface;
use Illuminate\Database\ConnectionResolverInterface;
use App\Foundation\Exceptions\ImproperlyConfigured;

class CapsuleMiddlewareFactory
{
    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        if ($container->has(ConnectionResolverInterface::class)) {
            $resolver = $container->get(ConnectionResolverInterface::class);
            return new CapsuleMiddleware($resolver);
        } else {
            throw new ImproperlyConfigured(sprintf(
                'cant create database repository.need %s registered to container',
                ConnectionResolverInterface::class
            ));
        }
    }
}
