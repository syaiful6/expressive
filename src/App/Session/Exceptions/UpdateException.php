<?php

namespace App\Session\Exceptions;

/**
* Occurs if we tries to update a session that was deleted.
*/
class UpdateException extends Exception
{
}
