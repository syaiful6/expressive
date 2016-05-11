<?php

namespace App\Foundation\Streams;

use DomainException;
use InvalidArgumentException;

/**
 * Buffered stream using an in-memory string buffer. Emulate php://memory stream,
 * The main difference with php://memory stream are this class is always readable
 * and writeable unless you call method close.
 */
class BufferedStream extends BaseStream
{
    /**
     * @var string hold the data written to this stream
     */
    protected $buffer = '';

    /**
     * @var integer hold the position pointer
     */
    protected $pos = 0;

    /**
     * Create new BufferedStream instance, you can give the initial buffer value
     * here. When $initial not string we will cast it to string.
     *
     * @param string|object $initial The initial content buffer
     * @return void
     */
    public function __construct($initial = null)
    {
        $buf = '';
        if ($initial !== null) {
            $buf .= (string) $initial;
        }
        $this->buffer = $buf;
    }

    /**
     * Returns the remaining contents in a string. Emulate the behaviour of PHP's
     * builtin stream_get_contents()
     *
     * @return string
     * @throws \RuntimeException if already closed
     */
    public function getContents()
    {
        $this->checkClosed();

        $str = substr($this->buffer, $this->pos);
        // After we get the remaining contents, we will advance the pointer position
        // to the end of the buffer, then we can return it.
        $this->pos = strlen($this->buffer);
        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return strlen($this->buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if ($this->isClosed) {
            return false;
        }
        return true;
    }

    /**
     * Write data to the stream.
     */
    public function write($string)
    {
        $this->checkClosed();
        if (! is_string($string)) {
            $string = (string) $string;
        }
        $n = strlen($string);
        // the string is empty, so return 0 as we dont write anything
        if ($n === 0) {
            return 0;
        }

        $pos = $this->pos;
        $slen = strlen($this->buffer);

        if ($pos > $slen) {
            //pad the string buffer with null bytes
            $pad = str_repeat("\0", $pos - $slen);
            $this->buffer .= $pad;
        }
        // if the position pointer is less then the buffer length then we need
        // to write this string in the middle of the pointer. If not we can add
        // this string to the end of buffer.
        if ($pos < $slen) {
            $list = [
                substr($this->buffer, 0, $pos), // from start to current position
                $string, // append the string here
                substr($this->buffer, $pos) // from current position to the end
            ];
            $this->buffer = join('', $list);
        } else {
            $this->buffer .= $string;
        }

        $this->pos += $n;

        return $n;
    }

    /**
     *
     */
    public function isReadable()
    {
        if ($this->isClosed) {
            return false;
        }

        return true;
    }

    /**
     * Read up to $length bytes from the object and return them. If the requested
     * $length + current position greater than buffer, it may return fewer bytes.
     *
     * @param integer $length
     * @return string
     */
    public function read($length)
    {
        $this->checkClosed();

        if ($length === null) {
            $length = -1;
        }
        if (!is_integer($length)) {
            throw new InvalidArgumentException(sprintf(
                'read expect an integer, %s give',
                gettype($length)
            ));
        }
        if ($length < 0) {
            $length = strlen($this->buffer);
        }
        // we dont have enough data, so return empty string
        if (strlen($this->buffer) <= $this->pos) {
            return '';
        }
        // prevent the new position pointer become greate than the buffer itself
        $newpos = min($this->pos + $length, strlen($this->buffer));
        $str = substr($this->buffer, $this->pos, strlen($this->buffer) - $newpos);
        $this->pos = $newpos;

        return $str;
    }

    /**
     *
     */
    public function isSeekable()
    {
        if ($this->isClosed) {
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->checkClosed();
        if (!is_integer($offset)) {
            throw new InvalidArgumentException(sprintf(
                'read expect an integer, %s give',
                gettype($offset)
            ));
        }
        if (! in_array($whence, [SEEK_CUR, SEEK_SET, SEEK_END])) {
            throw new DomainException(sprintf(
                'Unsupported whence value: %d. The supported are SEEK_SET(0)'.
                ' SEEK_CUR(1) and SEEK_END(2) constant',
                $whence
            ));
        }
        if ($whence === SEEK_SET) {
            if ($offset < 0) {
                throw new DomainException(sprintf(
                    'negative seek position %d',
                    $offset
                ));
            }
            $this->pos = $offset;
        } elseif ($whence === SEEK_CUR) {
            $this->pos = max(0, $this->pos + $offset);
        } else {
            $this->pos = max(0, strlen($this->buffer) + $offset);
        }

        return $this->pos;
    }

    /**
     *
     */
    public function truncate($size)
    {
        $this->checkClosed();
        if (!is_integer($offset)) {
            throw new \InvalidArgumentException(sprintf(
                'read expect an integer, %s give',
                gettype($offset)
            ));
        }

        if ($size < 0) {
            throw new DomainException('negative truncate position');
        }

        $this->buffer = substr($this->buffer, $size);
        return $size;
    }

    /**
     *
     */
    public function eof()
    {
        return $this->pos === strlen($this->buffer);
    }

    /**
     *
     */
    public function getMetadata($key = null)
    {
        $meta = [
            'seekable' => $this->isSeekable(),
            'stream_type' => 'buffered_stream',
            'eof' => $this->pos === strlen($this->buffer)
        ];

        if ($key === null) {
            return $meta;
        }

        if (!array_key_exists($key, $meta)) {
            return;
        }

        return $meta[$key];
    }

    /**
     *  This doesn't make sense on this stream
     */
    public function detach()
    {
    }
}
