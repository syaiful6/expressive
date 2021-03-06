<?php

namespace App\Database\Console;

use Interop\Container\ContainerInterface;
use App\Foundation\Exceptions\ImproperlyConfigured;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use App\Database\Migrator;

class ConsoleFactory
{
    /**
     *
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $name = str_replace(__NAMESPACE__.'\Commands\\', '', $requestedName);
        if (method_exists($this, "create$name")) {
            return call_user_func([$this, "create$name"], $container);
        } else {
            throw new ImproperlyConfigured("can\'t create $requestedName");
        }
    }

    /**
     *
     */
    public function createInstallCommand(ContainerInterface $container)
    {
        return new Commands\InstallCommand(
            $container->get(MigrationRepositoryInterface::class)
        );
    }

    /**
     *
     */
    public function createMigrate(ContainerInterface $container)
    {
        return new Commands\Migrate(
            $container->get(Migrator::class)
        );
    }

    /**
     *
     */
    public function createMakeMigration(ContainerInterface $container)
    {
        return new Commands\MakeMigration(
            $container->get('App\Database\MigrationCreator')
        );
    }
}
