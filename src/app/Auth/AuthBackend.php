<?php

namespace App\Auth;

interface AuthBackend
{
    /**
     * Authenticate provided params, return user if success. Return null
     * if support params but can't find the given user for the given params.
     *
     * @throws App\Auth\Exceptions\NotSupportedCredentials if not supported params
     * @throws App\Auth\Exceptions\PermissionDenied if this backend decided doesnot
     *         allow login this user at all
     * @return App\User\Model
     */
    public function authenticate(array $params);

    /**
     * Get the user model.
     */
    public function getUser($id);
}
