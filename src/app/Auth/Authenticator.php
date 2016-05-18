<?php

namespace App\Auth;

use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\Csrf;
use function Itertools\map;
use function Itertools\to_array;

class Authenticator
{
    const SESSION_KEY = '_AUTH_USER_ID';
    const BACKEND_SESSION_KEY = '_AUTH_USER_BACKEND';
    const HASH_SESSION_KEY = '_AUTH_USER_REMEMBER_TOKEN';

    /**
    * @var array|\Traversable string auth backend class name
    */
    protected $backends;

    /**
    * @var Interop\Container\ContainerInterface
    */
    protected $container;

    /**
     * @var Zend\EventManager\EventManagerInterface
     */
    protected $signal;

    /**
     * @param Interop\Container\ContainerInterface $container
     * @param App\Auth\AuthSignals
     * @param \Traversable|array $backends
     */
    public function __construct(
        ContainerInterface $container,
        AuthSignals $signal,
        $backends
    ) {
        $this->container = $container;
        $this->backends = $backends;
        $this->signal = $signal;
    }

    /**
     * If the given credentials are valid, return a User object.
     */
    public function authenticate(array $credentials)
    {
        foreach ($this->loadBackends(true) as list($cls, $backend)) {
            if (! $backend instanceof AuthBackend) {
                throw new \RuntimeException(sprintf(
                    '%s should implements %s to be able handle authenticate user',
                    $cls,
                    AuthBackend::class
                ));
            }
            try {
                $user = $backend->authenticate($credentials);
            } catch (Exceptions\NotSupportedCredentials $e) {
                // this backend not supported this credentials so continue
                continue;
            } catch (Exceptions\PermissionDenied $e) {
                // this backend says to stop in our tracks - this user should
                // not be allowed in at all.
                break;
            }
            if ($user === null) {
                continue;
            }
            $user->authBackend = $cls;

            return $user;
        }

        $this->signal->authenticateFailed($this->cleanUpCredentials($credentials));
    }

    /**
     *
     */
    public function login(Request $request, $user, $backend = null)
    {
        $session = $request->getAttribute('session', false);
        if (!$session) {
            throw new \RuntimeException(sprintf(
                'we need session middleware running before us!'
            ));
        }

        $sessionAuthHash = '';
        if (!$user) {
            $user = $request->getAttribute('user', false);
        }
        if (method_exists($user, 'getRememberToken')) {
            $sessionAuthHash = $user->getRememberToken();
            if (!$sessionAuthHash) {
                $sessionAuthHash = $this->refreshRememberToken($user, true);
            }
        }

        if ($session->contains(static::SESSION_KEY)) {
            $sessionUserId = (int) $session[static::SESSION_KEY];
            if ($sessionUserId !== (int) $user->getKey() || (
                $sessionAuthHash &&
                    ! hash_equals($sessionAuthHash, $session->get(static::HASH_SESSION_KEY))
            )) {
                $session->flush();
            }
        } else {
            $session->cycleId();
        }

        $backend = $backend ?: $user->authBackend;
        if (! $backend) {
            $backend = $this->validateSingleBackend();
        }

        $session[static::SESSION_KEY] = $user->getKey();
        $session[static::BACKEND_SESSION_KEY] = $backend;
        $session[static::HASH_SESSION_KEY] = $sessionAuthHash;
        Csrf::rotateToken(); // rotate the csrf token
        $request = $request->withAttribute('user', $user);
        $this->signal->userLoggedIn($user, $request);
        // return request so it can be used by middleware
        return $request;
    }

    /**
     *
     */
    public function logout(Request $request)
    {
        $user = $request->getAttribute('user', false);
        if (method_exists($user, 'isAuthenticate') && !$user->isAuthenticate()) {
            $user = null;
        }
        if ($user) {
            $this->refreshRememberToken($user, true);
        }
        $this->signal->userLoggedOut($user, $request);

        $session = $request->getAttribute('session', false);
        if ($session) {
            $session->flush();
        }
        // set the request user attribute as anonymous user
        return $request->withAttribute('user', new AnonymousUser());
    }

    /**
     *
     */
    public function user(Request $request)
    {
        $session = $request->getAttribute('session', false);
        $user = null;
        if (! $session) {
            $user = null;
        }
        try {
            $id = $session[static::SESSION_KEY];
            $backend = $session[static::BACKEND_SESSION_KEY];

            if (in_array($backend, to_array($this->backends))) {
                $backend = $this->container->get($backend);
                $user = $backend->getUser($id);

                if (method_exists($user, 'getRememberToken')) {
                    $token = $user->getRememberToken();
                    $sessionToken = $session[static::HASH_SESSION_KEY];
                    $verify = hash_equals($token, $sessionToken);
                    if (!$verify) {
                        $session->flush();
                        $user = null;
                    }
                }
            }
        } catch (\OutOfBoundsException $e) {
            // pass, it's mean we dont have an active user in session
        }

        return $user ?: new AnonymousUser();
    }

    /**
     *
     */
    public function getUser(Request $request)
    {
        return $this->user($request);
    }

    /**
     *
     */
    public function updateRememberToken(Request $request, $user)
    {
        $userRequest = $request->getAttribute('user', false);
        if (method_exists($user, 'getRememberToken')
            && $userRequest->getKey() === $user->getKey()
        ) {
            $session = $request->getAttribute('session', false);
            if ($session) {
                $session[static::HASH_SESSION_KEY] = $this->refreshRememberToken($user, true);
            }
        }
    }

    /**
     *
     */
    protected function refreshRememberToken($user, $save = true)
    {
        $user->setRememberToken($token = Str::random(60));
        if ($save) {
            $user->save();
        }
        return $token;
    }

    /**
     *
     */
    protected function validateSingleBackend()
    {
        $backends = is_array($this->backends) ? $this->backends : to_array($this->backends);
        if (count($backends) === 1) {
            return $backends[0];
        }
        throw new \RuntimeException(
            'You have multiple backends installed, therefore therefore must provide'
            .' the `backend` argument or set the `authBackend` attribute on the user.'
        );
    }

    /**
     *
     */
    protected function cleanUpCredentials(array $credentials)
    {
        $pattern = '/api|token|key|secret|password|signature/i';
        $subtitute = '********************';
        foreach (array_keys($credentials) as $key) {
            if (preg_match($pattern, $key)) {
                $credentials[$key] = $subtitute;
            }
        }
        return $credentials;
    }

    /**
     *
     */
    protected function loadBackends($includeCls = true)
    {
        return map(function ($backend) use ($includeCls) {
            if ($includeCls) {
                return [$backend, $this->container->get($backend)];
            }
            return $this->container->get($backend);
        }, $this->backends);
    }
}
