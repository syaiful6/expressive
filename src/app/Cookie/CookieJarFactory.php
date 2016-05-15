<?php

namespace App\Cookie;

use Interop\Container\ContainerInterface;

class CookieJarFactory
{
    /**
     *
     */
    public function __invoke(ContainerInterface $container)
    {
        $cookiejar = new CookieJar();
        if ($container->has('config')) {
            $config = $container->get('config');
            $session = $config['session'];
            if (isset($session['path']) && isset($session['domain'])) {
                $cookiejar->setDefaultPathAndDomain($session['path'], $session['domain']);
            }
        }
        return $cookiejar;
    }
}
