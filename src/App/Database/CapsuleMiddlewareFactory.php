<?php

namespace App\Database;

use Interop\Container\ContainerInterface;
use App\Foundation\Exceptions\ImproperlyConfigured;

class CapsuleMiddlewareFactory
{
    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        if ($container->has('config')) {
            $config = $container->get('config');
            $dbconfig = $config['database'];
            if (!is_array($dbconfig)) {
                throw new ImproperlyConfigured(
                    'Your db config must be an array'
                );
            }
            return new CapsuleMiddleware($dbconfig);
        } else {
            throw new ImproperlyConfigured(
                'You dont have any config!'
            );
        }
    }
}
