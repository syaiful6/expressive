<?php

namespace App\Functional;

class LazyObjectCallable
{
    /**
     * @var $wrapped
     */
    protected $wrapped;

    /**
     * the cached result
     */
    protected $cacheResult;

    /**
     *
     */
    public function __construct(callable $func)
    {
        $this->wrapped = $func;
    }

    /**
     *
     */
    public function __call(...$args)
    {
        if (!$this->cacheResult) {

        }
    }
}
