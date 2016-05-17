<?php

namespace App\Auth;

interface AuthBackend
{
    /**
     * Authenticate provided params, return user if success. Return null
     * if support params but can't find the given user for the given params.
     *
     * @throws App\Auth\Exceptions\NotSupportedCredentials if not supported params
     * @throws App\Auth\Exceptions\PermissionDenied if this user should not allowed
     *         in at all. Eg, they are blacklist
     * @return App\User\Model|null
     */
    public function authenticate(array $params);

    /**
     * Get the user model by their unique identifiers.
     *
     * @param mixin $id
     * @return App\User\Model|null
     */
    public function getUser($id);
}
