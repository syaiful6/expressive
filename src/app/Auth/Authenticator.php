<?php

namespace App\Auth;

use Illuminate\Support\Str;
use Zend\EventManager\EventManager;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use function Itertools\map;

class Authenticator implements EventManagerAwareInterface, EventsCapableInterface
{
    use EventManagerAwareTrait;

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
    protected $events;

    /**
     * @param Interop\Container\ContainerInterface $container
     * @param \Traversable|array $backends
     */
    public function __construct(ContainerInterface $container, $backends)
    {
        $this->container = $container;
        $this->backends = $backends;
    }

    /**
     * Lazy load Zend\EventManager\EventManagerInterface
     *
     * @return Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (! $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * If the given credentials are valid, return a User object.
     */
    public function authenticate(array $credentials)
    {
        foreach ($this->loadBackends() as list($cls, $backend)) {
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
            } catch(Exceptions\PermissionDenied $e) {
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

        $this->authenticateFailed($this->cleanUpCredentials($credentials));
    }

    /**
     *
     */
    public function login(Request $request, $user, $backend = null)
    {
        $session = $request->getAttribute('session');
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
            $sessionAuthHash = $this->getRememberToken();
        }

        if ($session->contains(static::SESSION_KEY)) {
            $sessionUserId = (int) $session[static::SESSION_KEY];
            if ($sessionUserId !== (int) $user->getKey() || (
                $sessionAuthHash &&
                    $session->get(static::HASH_SESSION_KEY) !== $sessionAuthHash
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
        $session['_CSRF_TOKEN'] = Str::random(32); // rotate them

        $request = $request->withAttribute('user', $user);
        $this->userLoggedIn($user, $request);
        // return request so it can be used by middleware
        return $request;
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
                return [$backend, $this->container->make($backend)];
            }
            return $this->container->make($backend);
        }, $this->backends);
    }
}
