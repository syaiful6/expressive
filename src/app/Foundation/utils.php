<?php

namespace App\Foundation;

use Illuminate\Support\Str;

function invoke($toCall, ...$args)
{
    if (is_callable($toCall)) {
        return $toCall(...$args);
    }

    return new $toCall(...$args);
}

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;

        case 'false':
        case '(false)':
            return false;

        case 'empty':
        case '(empty)':
            return '';

        case 'null':
        case '(null)':
            return;
    }

    if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
        return substr($value, 1, -1);
    }

    return $value;
}
