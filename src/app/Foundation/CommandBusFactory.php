<?php

namespace App\Foundation;

use League\Tactician\CommandBus;
use Illuminate\Contracts\Queue\Queue;
use League\Tactician\Handler\Locator\CallableLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Plugins\LockingMiddleware;
use Interop\Container\ContainerInterface as Container;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;

class CommandBusFactory
{
    /**
     *
     */
    public function __invoke(Container $container)
    {
        $locator = new CallableLocator(function ($commandName) use ($container) {
            $reflect = new \ReflectionClass($commandName);
            $namespace = $reflect->getNameSpace();
            $commandName = $namespace . '\Workers\\' . $reflect->getShortName();
            return $container->get($commandName);
        });

        $queued = new QueueCommandHandler($container->get(Queue::class));

        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new InvokeInflector()
        );

        return new CommandBus([new LockingMiddleware(), $queued, $handlerMiddleware]);
    }
}
