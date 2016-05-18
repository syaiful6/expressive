<?php

namespace App\Backends\Cache;

trait CacheKeyTrait
{
    /**
     *
     */
    protected function defaultCacheKey($key, $keyPrefix, $version)
    {
        return sprintf('%s:%s:%s', $key, $keyPrefix, $version);
    }

    /**
     *
     */
    protected function getKeyFunc($func)
    {
        if ($func !== null) {
            if (is_callable($func)) {
                return $func;
            }
        }
        return [$this, 'defaultCacheKey'];
    }
}
