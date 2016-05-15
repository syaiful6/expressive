<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Str;
use App\Cookie\CookieFactory;
use Zend\Diactoros\Uri;
use Headbanger\Set;

class Csrf
{
    const CSRF_KEY_LENGTH = 32;

    const CSRF_COOKIE_NAME = 'csrftoken';

    const CSRF_COOKIE_AGE = 31449600;

    /**
     *
     */
    protected $cookiejar;

    /**
    *
    */
    public function __construct(CookieFactory $cookiejar, array $configs)
    {
        $this->cookiejar = $cookiejar;
        $this->configs = $configs;
    }

    /**
     *
     */
    protected function rejectRequest($reason)
    {
        throw new TokenMismatchException($reason);
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $cookies = $request->getCookieParams();
        if (isset($cookies[static::CSRF_COOKIE_NAME])) {
            $csrftoken = $this->sanitizeToken($cookies[static::CSRF_COOKIE_NAME]);
            $request = $request->withAttribute('CSRF_COOKIE', $csrftoken);
        } else {
            $csrftoken = false;
            $request = $request->withAttribute('CSRF_COOKIE', static::getNewCsrfToken());
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE'])) {
            if ($request->getAttribute('DONT_ENFORCE_CSRF_CHECK', false)) {
                /**
                 * Mechanism to turn off CSRF checks for test suite.
                 */
                return $next($request, $response);
            }

            if (($uri = $request->getUri()->getScheme()) === 'https') {
                $server = $request->getServerParams();
                $referer = isset($server['HTTP_REFERER'])
                    ? $server['HTTP_REFERER']
                    : false;
                if (!$referer) {
                    $this->rejectRequest(
                        'Referer checking failed - no Referer.'
                    );
                }
                $goodReferrer = sprintf('https://$s:%s/', $uri->getHost(), $uri->getPort());
                if (!$this->sameOrigin($referer, $goodReferrer)) {
                    $reason = sprintf(
                        'Referer checking failed - %s does not match %s.',
                        $referer,
                        $goodReferrer
                    );
                    $this->rejectRequest($reason);
                }
            }
            if (!$csrftoken) {
                $this->rejectRequest('CSRF cookie not set.');
            }

            $requestcsrftoken = '';
            if ($request->getMethod() === 'POST') {
                $post = $this->getParsedBody();
                $requestcsrftoken = isset($post['csrfmiddlewaretoken'])
                    ? $post['csrfmiddlewaretoken']
                    : '';
            }
            if ($requestcsrftoken === '') {
                $requestcsrftoken = $request->getHeader('X-CSRFTOKEN');
            }
            if (! $this->compareCsrfToken($requestcsrftoken, $csrftoken)) {
                $this->rejectRequest('CSRF token missing or incorrect.');
            }
        }

        $response = $next($request, $response);

        if (!$request->getAttribute('CSRF_COOKIE_USED')) {
            return $response;
        }

        if (!$request->getAttribute('CSRF_COOKIE')) {
            return $response;
        }

        $cookie = $this->cookiejar->make(
            self::CSRF_COOKIE_NAME,
            $request->getAttribute('CSRF_COOKIE'),
            null,
            self::CSRF_COOKIE_AGE,
            $this->settings['path'],
            $this->settings['domain'],
            $this->settings['secure'],
            false
        );
        $response = $this->patchVaryHeader($response);
        return $response
            ->withAddedHeader('Set-Cookie', $cookie)
            ->withoutAttribute('CSRF_COOKIE')
            ->withoutAttribute('CSRF_COOKIE_USED');
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
    protected function compareCsrfToken($token1, $token2)
    {
        if (! is_string($token1) || ! is_string($token2)) {
            return false;
        }

        return hash_equals($token1, $token2);
    }

    /**
     *
     */
    protected function sameOrigin($uri1, $uri2)
    {
        list($uri1, $uri2) = [new Uri($uri1), new Uri($uri2)];

        $o1 = [$uri1->getScheme(), $uri1->getHost(),
            $uri1->getPort() ?: $this->protocolToPort($uri1->getScheme($uri1->getScheme()))];
        $o2 = [$uri2->getScheme(), $uri2->getHost(),
            $uri2->getPort() ?: $this->protocolToPort($uri1->getScheme($uri2->getScheme()))];

        return $o1 === $o2;
    }

    /**
     *
     */
    protected function protocolToPort($protocol)
    {
        $map = [
            'http' => 80,
            'https' => 443,
        ];
        return $map[$protocol];
    }

    /**
     *
     */
    public static function rotateToken(ServerRequestInterface $request)
    {
        return $request
            ->withAttribute('CSRF_COOKIE_USED', true)
            ->withAttribute('CSRF_COOKIE', self::getNewCsrfToken());
    }

    /**
     *
     */
    protected static function getNewCsrfToken()
    {
        return Str::random(static::CSRF_KEY_LENGTH);
    }

    /**
     *
     */
    public static function getToken(ServerRequestInterface $request)
    {
        $request = $request->withAttribute('CSRF_COOKIE_USED', true);
        return [$request, $request->getAttribute('CSRF_COOKIE')];
    }

    /**
     *
     */
    protected function sanitizeToken($token)
    {
        if (strlen($token) > static::CSRF_KEY_LENGTH) {
            return static::getNewCsrfToken();
        }
        $token = preg_replace('/[^a-zA-Z0-9]+/', '', $token);
        if ($token === '') {
            return static::getNewCsrfToken();
        }

        return $token;
    }
}
