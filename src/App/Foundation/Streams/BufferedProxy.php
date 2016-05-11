<?php

namespace App\Foundation\Streams;

use RuntimeException;

/**
* A buffered proxy stream with an underlying raw stream. As the name suggest,
* this passes most requests on to the underlying raw stream.
*/
trait BufferedProxy
{
    /**
     * The underlying raw stream.
     *
     * @var Lambene\Streamlib\BaseStream
     */
    protected $raw;

    /**
     *
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $newPosition = $this->raw->seek($offset, $whence);
        if ($newPosition < 0) {
            throw new RuntimeException('seek() return invalid position');
        }

        return $newPosition;
    }

    /**
     *
     */
    public function tell()
    {
        $pos = $this->raw->tell();
        if ($pos < 0) {
            throw new RuntimeException('tell() return invalid position');
        }

        return $pos;
    }

    /**
     *
     */
    public function truncate($size)
    {
        $this->flush();

        return $this->raw->truncate();
    }

    /**
     *
     */
    public function flush()
    {
        if ($this->isClosed) {
            throw new RuntimeException('flush on closed stream');
        }
        $this->raw->flush();
    }

    /**
     *
     */
    public function close()
    {
        if ($this->raw !== null && !$this->isClosed) {
            try {
                $this->flush();
            } finally {
                $this->raw->close();
            }
        }
    }

    /**
     *
     */
    public function detach()
    {
        if ($this->raw === null) {
            return; // already detached
        }

        $this->flush();
        $raw = $this->raw;
        $this->raw = null;

        return $raw; // maybe call raw's detach method here?
    }

    /**
     *
     */
    public function isSeekable()
    {
        return $this->raw->isSeekable();
    }

    /**
     *
     */
    public function isReadable()
    {
        return $this->raw->isReadable();
    }

    /**
     *
     */
    public function isWritable()
    {
        return $this->raw->isReadable();
    }

    /**
     *
     */
    public function eof()
    {
        return $this->raw->eof();
    }
}
