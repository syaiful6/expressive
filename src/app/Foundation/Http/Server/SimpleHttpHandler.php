<?php

namespace App\Foundation\Http\Server;

use Zend\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class SimpleHttpHandler extends BaseHttpHandler
{
    protected $stdout;

    protected $stderr;

    protected $httpVersion = '1.0';
    /**
     *
     */
    public function __construct(
        StreamInterface $stdout = null,
        StreamInterface $stderr = null
    ) {

        $this->stdout = $stdout ?: new Stream('php://output', 'wb');
        $this->stderr = $this->stderr ?: new Stream('php://stderr', 'wb');
    }

    /**
     *
     */
    protected function sendHeaders()
    {
        $this->cleanUpHeaders();
        $this->isHeaderSent = true; // mark the header
        header(sprintf(
            'HTTP/%s %s',
            $this->httpVersion,
            $this->status
        ));
        foreach ($this->headers->items() as list($name, $value)) {
            $name = $this->filterHeader($name);
            $first = true;
            // psr use format ['key' => ['v1', v2]]
            foreach ((array) $value as $v) {
                header(sprintf('%s: %s', $name, $v), $first);
                $first = false;
            }
        }
    }

    /**
     * Loops through the output buffer, flushing each, before emitting
     * the response.
     *
     * @param int|null $maxBufferLevel Flush up to this buffer level.
     */
    protected function flush($maxBufferLevel = null)
    {
        if (method_exists($this->stdout, 'flush')) {
            $this->stdout->flush();

            return;
        }
        if (null === $maxBufferLevel) {
            $maxBufferLevel = ob_get_level();
        }

        while (ob_get_level() > $maxBufferLevel) {
            ob_end_flush();
        }
    }

    /**
     *
     */
    protected function doWrite($data)
    {
        $this->stdout->write($data);
    }

    /**
     *
     */
    protected function getStdin()
    {
        return $this->request->getBody();
    }

    /**
     *
     */
    protected function getStdout()
    {
        return $this->stdout;
    }

    /**
     *
     */
    protected function getStdErr()
    {
        return $this->stderr;
    }

    /**
     * Filter a header name to wordcase.
     *
     * @param string $header
     *
     * @return string
     */
    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);

        return str_replace(' ', '-', $filtered);
    }

    /**
     *
     */
    protected function logException($e)
    {
        $stderr = $this->getStdErr();
        $stderr->write($e->getTraceAsString());
    }
}
