<?php

namespace App\Auth;

interface AuthBackend
{
    /**
     * Authenticate provided credentials, return user if success. Return null
     * if support credentials but can't find the user.
     *
     * @throws App\Auth\Exceptions\NotSupportedCredentials if not supported params
     * @throws App\Auth\Exceptions\PermissionDenied if this user should not allowed
     *         in at all. Eg, they are blacklist
     * @return App\User\Model|null
     */
    public function authenticate(array $credentials);

    /**
     * Get the user model by their unique identifiers.
     *
     * @param mixin $id
     * @return App\User\Model|null
     */
    public function getUser($id);
}
