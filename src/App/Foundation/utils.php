<?php

namespace App\Foundation;

function invoke($toCall, ...$args)
{
    if (is_callable($toCall)) {
        return $toCall(...$args);
    }

    return new $toCall(...$args);
}
