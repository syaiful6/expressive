<?php

namespace App\Foundation\Http\Server;

use Exception;
use Throwable;
use Countable;
use LogicException;
use Zend\Diactoros\ServerRequestFactory;
use function App\Foundation\invoke;

abstract class BaseHttpHandler
{
    protected $request;

    protected $status;

    protected $result;

    protected $isHeaderSent = false;

    protected $headers;

    protected $headersClass = Headers::class;

    protected $sentLen = 0;

    protected $errorStatus = '500 Internal Server Error';
    protected $errorHeaders = [['Content-Type', 'text/plain']];
    protected $errorBody = 'A server error occurred.  Please contact the administrator.';

    /**
     *
     */
    public function serve($app)
    {
        try {
            $this->setupRequest();
            $this->result = invoke($app, $this->request, [$this, 'startResponse']);
            $this->finishResponse();
        } catch (Exception $e) {
            try {
                $this->handleError($e);
            } catch (Exception $er) {
                $this->close();
                throw $er;
            }
        }
    }

    /**
     *
     */
    protected function setupRequest(...$args)
    {
        $this->request = ServerRequestFactory::fromGlobals(...$args);
    }

    /**
     *
     */
    protected function finishResponse()
    {
        try {
            if (! $this->isResultFile() || ! $this->sendFile()) {
                foreach ($this->result as $data) {
                    $this->write($data);
                }
                $this->finishContent();
            }
        } finally {
            $this->close();
        }
    }

    /**
     *
     */
    public function startResponse($status, $headerResponse, $excInfo = null)
    {
        if ($excInfo) {
            try {
                if ($this->isHeaderSent) {
                    throw $excInfo;
                }
            } finally {
                $excInfo = null;
            }
        } elseif ($this->headers !== null) {
            throw new LogicException('Headers already set!');
        }

        $this->status = $status;
        $headersClass = $this->headersClass;
        $this->headers = new $headersClass($headerResponse);
        $status = (string) $status;

        assert(strlen($status) >= 4, 'Status must be at least 4 characters');
        assert(is_numeric(substr($status, 0, 3)), 'Status message must begin w/3-digit code');
        assert($status[3] === ' ', 'Status message must have a space after code');

        return function ($data) {
            $this->write($data);
        };
    }

    /**
     *
     */
    public function write($data)
    {
        assert(is_string($data), 'data must be string');

        if (! $this->status) {
            throw new LogicException('write() before startResponse()');
        } elseif (! $this->isHeaderSent) {
            $this->sentLen = strlen($data);
            $this->sendHeaders();
            $this->flush();
        } else {
            $this->sentLen += strlen($data);
        }

        $this->doWrite($data);
        $this->flush();
    }

    /**
     *
     */
    protected function sendFile()
    {
    }

    /**
     *
     */
    protected function finishContent()
    {
        if (! $this->isHeaderSent) {
            $this->headers->setDefault('Content-Length', '0');
            $this->sendHeaders();
        }
    }

    /**
     *
     */
    protected function close()
    {
        try {
            if (method_exists($this->result, 'close')) {
                $this->result->close();
            }
        } finally {
            $this->headers = $this->request = $this->status = $this->result = null;

            $this->isHeaderSent = false;

            $this->sentLen = 0;
        }
    }

    /**
     * send the headers to the browser.
     */
    abstract protected function sendHeaders();

    /**
     *
     */
    protected function isResultFile()
    {
        return $this->result instanceof FileWrapper;
    }

    /**
     *
     */
    protected function logException($e)
    {
        try {
            $stderr = $this->getStdErr();
            $stderr->write($e->getTraceAsString());
        } finally {
            $e = null;
        }
    }

    /**
     *
     */
    protected function handleError($e)
    {
        $this->logException($e);
        if (! $this->isHeaderSent) {
            $this->result = $this->errorOutput($this->request, [$this, 'startResponse']);
            $this->finishResponse();
        }
    }

    /**
     *
     */
    private function errorOutput($request, $startResponse, $excInfo = null)
    {
        $startResponse($this->errorStatus, $this->errorHeaders, $excInfo);

        return [$this->errorBody];
    }

    /**
     *
     */
    protected function cleanUpHeaders()
    {
        if (! isset($this->headers['Content-Length'])
            && null !== $this->headers['Content-Length']) {
            $this->setContentLength();
        }
    }

    /**
     *
     */
    protected function setContentLength()
    {
        if ($this->result) {
            if (is_array($this->result) || $this->result instanceof Countable) {
                $block = count($this->result);
                if ($block === 1) {
                    $this->headers['Content-Length'] = (string) $this->sentLen;
                }
            }
        }
    }

    /**
     *
     */
    abstract protected function doWrite($data);

    /**
     *
     */
    abstract protected function flush();

    /**
     *
     */
    abstract protected function getStdin();

    /**
     *
     */
    abstract protected function getStdout();

    /**
     *
     */
    abstract protected function getStdErr();
}
