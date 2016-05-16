<?php

namespace App\Middleware;

use App\Cookie;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class GenericMiddlewareFactory
{
    /**
     *
     */
    public function __invoke(
        ContainerInterface $container,
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
            throw new ImproperlyConfigured("can\'t create $requestedName");
        }
    }

    /**
     *
     */
    private function createCsrf($container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $setting = isset($config['session']) ? $config['session'] : [];
        if ($container->has(Cookie\CookieFactory::class)) {
            $cookieJar = $container->get(Container\CookieFactory::class);
        } elseif ($container->has(Cookie\QueueingCookieFactory::class)) {
            $cookieJar = $container->get(Cookie\QueueingCookieFactory::class);
        } else {
            throw new \RuntimeException(
                'cant create csrf middleware! no cookie jar on container'
            );
        }

        return new Csrf($cookieJar, $setting);
    }

    /**
     *
     */
    private function createContextProcessor($container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $setting = isset($config['templates']) ? $config['templates'] : [];
        $processors = isset($setting['context_processors']) ? $setting['context_processors'] : [];
        return new ContextProcessor($container, $processors);
    }
}
