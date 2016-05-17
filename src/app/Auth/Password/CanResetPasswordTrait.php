<?php

namespace App\Auth\Password;

trait CanResetPasswordTrait
{
    /**
    *
    */
    public function getEmail()
    {
        return $this->email;
    }
}
