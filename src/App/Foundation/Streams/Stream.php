<?php

namespace App\Foundation\Streams;

use InvalidArgumentException;
use RuntimeException;

class Stream extends BaseStream
{
    protected $stream;

    protected $resource;

    /**
     *
     */
    public function __construct($stream, $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }

    /**
     *
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }
        $resource = $this->detach();
        fclose($resource);
        parent::close();
    }

    /**
     *
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     *
     */
    public function attach($stream, $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }

    /**
     *
     */
    public function getSize()
    {
        if (!$this->resource) {
            return;
        }

        $stats = fstat($this->resource);
        return $stats['size'];
    }

    /**
     *
     */
    public function tell()
    {
        if (! $this->resource) {
            throw new RuntimeException(
                'No resource available; cannot tell position'
                );
        }

        $result = ftell($this->resource);
        if (! is_int($result)) {
            throw new RuntimeException(
                'Error occurred during tell operation'
                );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if (! $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (! $this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if (0 !== $result) {
            throw new RuntimeException('Error seeking within stream');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if (! $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (
            strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (! $this->resource) {
            throw new RuntimeException(
                'No resource available; cannot write'
                );
        }

        if (! $this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new RuntimeException('Error writing to stream');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        if (! $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * Read and return a line of bytes from the stream. If size is specified,
     * at most size bytes will be read. This implementation only support \n
     * terminator currently,
     *
     * @param integer $size -1 mean read all
     * @return string
     * @throws \InvalidArgumentException if size is not an integer
     * @throws \RuntimeException if the underlying stream closed
     */
    public function readLine($size=-1)
    {
        if (! $this->resource) {
            return '';
        }
        if ($size <= 0) {
            $size = 8192;
        }

        return fgets($this->resource, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot read');
        }

        if (! $this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fread($this->resource, $length);

        if (false === $result) {
            throw new RuntimeException('Error reading stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (! $this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = stream_get_contents($this->resource);
        if (false === $result) {
            throw new RuntimeException('Error reading from stream');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (! $this->isReadable()) {
            return '';
        }

        try {
            $this->seek(0);
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     *
     */
    protected function setStream($stream, $mode = 'r')
    {
        $resource = $stream;
        $error = null;

        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                $error = $e;
            }, E_WARNING);

            $resource = fopen($stream, $mode);
            restore_error_handler();
        }

        if ($error) {
            throw new InvalidArgumentException(
                'Invalid stream reference provided'
                );
        }

        if (! is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }

        if ($stream !== $resource) {
            $this->stream = $stream;
        }

        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return stream_get_meta_data($this->resource);
        }

        $metadata = stream_get_meta_data($this->resource);
        if (! array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        if (! $this->resource) {
            return true;
        }

        return feof($this->resource);
    }
}
