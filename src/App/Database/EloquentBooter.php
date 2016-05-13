<?php

namespace App\Database;

use Illuminate\Database\Capsule\Manager;
use App\Foundation\Exceptions\ImproperlyConfigured;

class EloquentBooter
{
    /**
     *
     */
    public static function boot(array $configs)
    {
        $manager = new Manager();
        $default = $configs['default'];
        if (!is_array($connections = $configs['connections'])) {
            throw new ImproperlyConfigured(
                'Your dont have any db connections on your config.'
            );
        }
        foreach ($connections as $name => $config) {
            $manager->addConnection($config, $name);
            if ($default === 'name') {
                $manager->addConnection($config);
            }
        }
        if (isset($configs['fetch'])) {
            $manager->setFetchMode($configs['fetch']);
        }

        $manager->bootEloquent();
        // then make them available globally.
        $manager->setAsGlobal();
    }
}
