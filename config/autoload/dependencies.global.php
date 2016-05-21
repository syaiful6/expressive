<?php
use App\Foundation\Application;
use App\Foundation\AppFactory;
use Zend\Expressive\Helper;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            Application::class => AppFactory::class,
            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,
            App\Session\Store::class => App\Session\StoreFactory::class,
            'App\Cookie\QueueingCookieFactory' => 'App\Cookie\CookieJarFactory',
            'App\Translation\LoaderInterface' => 'App\Translation\TranslatorFactory',
            'App\Cache\Backends\BaseCache' => 'App\Cache\CacheFactory',
            'App\Cache\RateLimiter' => 'App\Cache\CacheFactory',
            'Symfony\Component\Translation\TranslatorInterface' =>
                'App\Translation\TranslatorFactory',
            'App\Validation\PresenceVerifierInterface' =>
                'App\Validation\ValidationServiceFactory',
            'Illuminate\Contracts\Validation\Factory' =>
                'App\Validation\ValidationServiceFactory',
            'Illuminate\Contracts\Encryption\Encrypter' =>
                'App\Foundation\EncrypterFactory',
            'App\Flash\Storage\BaseStorage' => 'App\Flash\FlashServiceFactory',
            'App\Flash\FlashMessageInterface' => 'App\Flash\FlashServiceFactory',
            'App\Flash\FlashMessageMiddleware' => 'App\Flash\FlashServiceFactory',
            'League\Tactician\CommandBus' => 'App\Foundation\CommandBusFactory',
        ],
        'abstract_factories' => [
            'App\Foundation\AbstractFactoryReflection'
        ],
        'initializers' => [
            'App\Foundation\ValidatorFactoryAwareInitializer'
        ]
    ],
];
