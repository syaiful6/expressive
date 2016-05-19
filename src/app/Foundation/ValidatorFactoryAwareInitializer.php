<?php

namespace App\Foundation;

use Interop\Container\ContainerInterface;
use App\Validation\ValidatorFactoryAwareInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Illuminate\Contracts\Validation\Factory as FactoryContract;

class ValidatorFactoryAwareInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof ValidatorFactoryAwareInterface) {
            $instance->setValidatorFactory($container->get(FactoryContract::class));
        }
    }
}
