<?php

namespace App\Session;

use Interop\Container\ContainerInterface;
use App\Session\Backends\SessionBackendInterface;
use App\Session\Backends\File as FileBackend;

class StoreFactory
{
    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        $backend = $container->has(SessionBackendInterface::class)
            ? $container->get(SessionBackendInterface::class)
            : new FileBackend();

        $config = $container->has('config') ? $container->get('config') : [];
        $setting = isset($config['session']) ? $config['session'] : [];
        $name   = isset($setting['cookie']) ? $setting['cookie'] : 'expressive-session';
        return new Store($name, $backend);
    }
}
