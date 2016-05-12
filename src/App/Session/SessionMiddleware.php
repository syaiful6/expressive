<?php

namespace App\Session;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\RedirectResponse;
use App\Session\Exceptions\UpdateException;
use App\Foundation\Http\Cookie;
use Headbanger\Set;

class SessionMiddleware
{
    protected $store;

    protected $configs;

    /**
     *
     */
    public function __construct(Store $store, array $configs = [])
    {
        $this->store = $store;
        $this->configs = $configs;
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $session = $this->store;
        $cookies = $request->getCookieParams();
        $sessionId = isset($cookies[$session->getName()])
            ? $cookies[$session->getName()]
            : '';
        $session->setId($sessionId);

        $request = $request->withAttribute('session', $session);
        // process the next middleware
        $response = $next($request, $response);
        // see what happen there
        $session = $request->getAttribute('session', $session);
        $accessed = $session->isAccessed();
        $modified = $session->isModified();
        $empty = $session->isEmpty();

        if ($accessed) {
            $response = $this->patchVaryHeader($response);
        }

        if ($modified && !$empty) {
            $response = $this->addCookieToResponse($response, $session);

            if ((int) $response->getStatusCode() !== 500) {
                try {
                    $session->save();
                } catch (UpdateException $e) {
                    return new RedirectResponse($request->getUri()->getPath());
                }
            }
        }

        $this->collectGarbage($session);

        return $response;
    }

    /**
     *
     */
    protected function addCookieToResponse($response, $session)
    {
        $cookie = new Cookie();
        $cookie[$name = $session->getName()] = $session->getId();
        $cookie[$name]['expiry'] = $this->getCookieExpirationDate();
        $cookie[$name]['path'] = $this->configs['path'] ?: '/';
        if (isset($this->configs['domain'])) {
            $cookie[$name]['domain'] = $this->configs['domain'];
        }
        $cookie[$name]['secure'] = isset($this->configs['secure'])
            ? $this->configs['secure']
            : false;
        if (isset($this->configs['httponly'])) {
            $cookie[$name]['httponly'] = $this->configs['httponly'];
        }
        $out = $cookie->getOutput(null, '', '');

        return $response->withAddedHeader('Set-Cookie', $out);
    }

    /**
     *
     */
    protected function getCookieExpirationDate()
    {
        $configs = $this->configs;

        if ($configs['expire_on_close']) {
            return 0;
        }

        $expiry = new \DateTime('now', new \DateTimeZone(\DateTimeZone::UTC));
        $expiry->modify('+' . $config['lifetime'] . ' minute');

        return $expiry;
    }

    /**
     *
     */
    protected function patchVaryHeader($response)
    {
        if ($response->hasHeader('Vary')) {
            $vary = $response->getHeader('Vary');
        } else {
            $vary = [];
        }

        $set = new Set(array_map('strtolower', $vary));
        $newAdded = array_filter(['cookie', ], function ($item) use ($set) {
            return ! $set->contains($item);
        });
        $vary = join(', ', array_merge($vary, $newAdded));
        return $response->withHeader('Vary', $vary);
    }

    /**
     *
     */
    protected function collectGarbage($session)
    {
        $lottery = $this->configs['lottery'];
        if ($this->configHitsLottery($lottery)) {
            $session->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param  array  $config
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        return random_int(1, $config[1]) <= $config[0];
    }
}
