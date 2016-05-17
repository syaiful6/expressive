<?php

namespace App\Auth;

use Interop\Container\ContainerInterface as Container;

class AuthServiceFactory
{
    /**
     *
     */
    public function __invoke(
        Container $container,
        $requestedName,
        array $options = null
    ) {
        $name = str_replace(__NAMESPACE__, '', $requestedName);
        if ($name[0] === '\\') {
            $name = substr($name, 1);
        }
        if (method_exists($this, "create$name")) {
            return call_user_func([$this, "create$name"], $container);
        } else {
            throw new \RuntimeException("can\'t create $requestedName");
        }
    }

    /**
     *
     */
    protected function createAuthenticator(Container $container)
    {
        $backends = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            $backends = isset($config['auth']) ? $config['auth']['backends'] : nulll;
        }

        if (empty($backends)) {
            $backends = [ModelBackend::class];
        }
        if ($container->has(AuthSignals::class)) {
            $signal = $container->get(AuthSignals::class);
        } else {
            throw new \RuntimeException('you maybe forget to install AuthSignals on config');
        }
        return new Authenticator($container, $signal, $backends);
    }

    /**
     *
     */
    protected function createAuthenticationMiddleware(Container $container)
    {
        if ($container->has(Authenticator::class)) {
            return new AuthenticationMiddleware($container->get(Authenticator::class));
        }

        throw new \RuntimeException('missing authenticator class on container');
    }

    /**
     *
     */
    protected function createModelBackend(Container $container)
    {
        if ($container->has('config')) {
            $config = $container->get('config');
            $model = isset($config['auth']) ? $config['auth']['model'] : User::class;
        } else {
            $model = User::class;
        }

        return new ModelBackend($model);
    }
}
