<?php

namespace App\Cookie;

class CookieJar implements QueueingCookieFactory
{
    /**
     * The default path
     *
     * @var string
     */
    protected $path;

    /**
     * The default domain
     */
    protected $domain;

    /**
     *
     */
    protected $secure = false;

    /**
     * The cookies
     *
     * @var App\Cookie\Cookie
     */
    protected $cookies;

    /**
     *
     */
    public function __construct(Cookie $cookies = null)
    {
        $this->cookies = $cookies ?: new Cookie();
    }

    /**
     *
     */
    public function make(
        $name,
        $value,
        $expires = null,
        $maxAge = null,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        $oldCookie = $this->cookies;
        $this->cookies = new Cookie();
        $this->queue(
            $name,
            $value,
            $expires,
            $maxAge,
            $path,
            $domain,
            $secure,
            $httpOnly
        );

        $output = $this->cookies->getOutput(null, '', '');
        $this->cookies = $oldCookie;
        return $output;
    }

    /**
     *
     */
    public function forget($name, $path = null, $domain = null)
    {
        return $this->make($name, '', null, 0, $path, $domain);
    }

    /**
     *
     */
    public function forever(
        $name,
        $value,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        return $this->make($name, $value, 2628000, null, $path, $domain, $secure, $httpOnly);
    }

    /**
     *
     */
    public function queue(
        $name,
        $value,
        $expires = null,
        $maxAge = null,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        list($path, $domain, $secure) = $this->getPathAndDomain($path, $domain, $secure);
        $this->cookies[$name] = $value;

        if ($expires !== null) {
            if ($expires instanceof \DateTime || $expires instanceof \DateTimeImmutable) {
                $utc = new DateTimeZome('UTC');
                $expires = $expires->setTimeZone($utc);
                $utcNow = new \DateTime('now', $timezone);
                // now we can substract it
                $maxAge = max(0, $expires->getTimestamp() - $utcNow->getTimestamp());
                $expires = null;
            } else {
                $this->cookies[$name]['expires'] = $expires;
            }
        }
        if ($maxAge !== null) {
            $this->cookies[$name]['max-age'] = $maxAge;
            // IE requires expires, so set it if hasn't been already.
            if (!$expires) {
                $this->cookies[$name]['expires'] = time() + $maxAge;
            }
        }
        if ($path !== null) {
            $this->cookies[$name]['path'] = $path;
        }
        if ($domain !== null) {
            $this->cookies[$name]['domain'] = $domain;
        }
        if ($secure) {
            $this->cookies[$name]['secure'] = true;
        }
        if ($httpOnly) {
            $this->cookies[$name]['httponly'] = true;
        }
    }

    /**
     *
     */
    public function unqueue($name)
    {
        unset($this->cookies[$name]);
    }

    /**
     *
     */
    public function contains($name)
    {
        return $this->cookies->contains($name);
    }

    /**
     * Get the path and domain, or the default values.
     *
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return array
     */
    protected function getPathAndDomain($path, $domain, $secure = false)
    {
        return [$path ?: $this->path, $domain ?: $this->domain, $secure ?: $this->secure];
    }

    /**
     * Set the default path and domain for the jar.
     *
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @return $this
     */
    public function setDefaultPathAndDomain($path, $domain, $secure = false)
    {
        list($this->path, $this->domain, $this->secure) = [$path, $domain, $secure];

        return $this;
    }

    /**
     * Get the cookies which have been queued for the next request.
     *
     * @return array
     */
    public function getQueuedCookies()
    {
        $out = [];
        foreach ($this->cookies->values() as $cookie) {
            array_push($out, $cookie->getOutput(null, ''));
        }
        return $out;
    }
}
