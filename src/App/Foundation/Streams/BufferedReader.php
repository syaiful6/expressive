<?php

namespace App\Foundation\Streams;

use RuntimeException;

class BufferedReader extends BaseStream
{
    use BufferedProxy;

    const DEFAULT_BUFFER_SIZE = 8192;

    protected $bufferSize;

    protected $readBuffer;

    protected $readPos;

    /**
     *
     */
    public function __construct($raw, $bufferSize=self::DEFAULT_BUFFER_SIZE)
    {
        if (!$raw->isReadable()) {
            throw new RuntimeException('raw stream must be readable');
        }

        $this->raw = $raw;
        if ($bufferSize <= 0) {
            throw new RuntimeException('invalid buffer size');
        }

        $this->bufferSize = $bufferSize;
        $this->resetReadBuffer();
    }

    /**
     *
     */
    public function read($size)
    {
        $noData = '';
        $buffer = $this->readBuffer;
        $pos = $this->readPos;

        $available = strlen($buffer) - $pos;
        if ($size <= $available) {
            $this->readPos += $size;
            return substr($buffer, $pos, $size);
        }
        // we dont have enough data read from underlying raw stream
        $chunks = [substr($buffer, $pos)];
        $wanted = max($this->bufferSize, $size);
        while ($available < $size) {
            $chunk = $this->raw->read($wanted);
            if (in_array($chunk, ['', null])) {
                $noData = $chunk;
                break;
            }
            $available += strlen($chunk);
            $chunks[] = $chunk;
        }

        $size = min($size, $available);
        $out = join('', $chunks);
        $this->readBuffer = substr($out, $size);
        $this->readPos = 0;
        return empty($out) ? $noData : substr($out, 0, $size);
    }

    /**
     *
     */
    public function peek($size)
    {
        $want = min($size, $this->bufferSize);
        $have = strlen($this->readBuffer) - $this->readPos;
        if ($have < $want || $have <= 0) {
            $toRead = $this->bufferSize - $have;
            // maybe we need wrap this on loop, in case the raw return false?
            $current = $this->raw->read($toRead);
            if ($current) {
                $this->readBuffer = substr($this->readBuffer, $this->readPos);
                $this->readBuffer .= $current;
                $this->readPos = 0;
            }
        }
        return substr($this->readBuffer, $this->readPos);
    }

    /**
     *
     */
    public function tell()
    {
        return $this->raw->tell() - strlen($this->readBuffer) + $this->readPos;
    }

    /**
     *
     */
    public function isWritable()
    {
        return false;
    }

    /**
     *
     */
    protected function resetReadBuffer()
    {
        $this->readBuffer = '';
        $this->readPos = 0;
    }
}
