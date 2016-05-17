<?php

namespace App\Auth\Exceptions;

/**
 * throwing by backend to stop the attempts to authenticate user - the user should
 * not be allowed in at all.
 */
class PermissionDenied extends \Exception
{
}
