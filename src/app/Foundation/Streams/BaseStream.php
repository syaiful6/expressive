<?php

namespace App\Foundation\Streams;

use RuntimeException;
use IteratorAggregate;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * The BaseStream class that all stream classes inherited. Provides dummy
 * implementations that child class may override. This class act as stream
 * that cannot be read, written or seeked.
 *
 * BaseStream and its subsclass implements IteratorAggregate, thanks to Generator
 * to make it possible. This mean, you can iterate this object, and it will yielding
 * the lines in a stream.
 */
abstract class BaseStream implements StreamInterface, IteratorAggregate
{
    /**
     * @var boolean
     */
    protected $isClosed = false;

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     * It surpress all Exception because PHP doesn't allow it.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#object.tostring
     */
    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources. No effect if the stream
     * already closed.
     *
     * @return void
     */
    public function close()
    {
        if (!$this->isClosed) {
            // attempt to close this stream by flush
            // it first. If flush throw an exception leave
            // it be, and close.
            try {
                $this->flush();
            } finally {
                $this->isClosed = true;
            }
        }
    }

    /**
     * Get the size of the stream if known. Default to null
     *
     * @return int|null
     */
    public function getSize()
    {
    }

    /**
     * Flush write buffers, if applicable.
     *
     * @return boolean True if success, false otherwise
     * @throws RuntimeException if the stream already closed
     */
    public function flush()
    {
        $this->_checkClosed();
        return true;
    }

    /**
     * Returns the current position of the file read/write pointer.
     * Let the seek implementation do it.
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        return $this->seek(0, SEEK_CUR);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->unSupportedOperation(__METHOD__);
    }

    /**
     * Seek to the beginning of the stream. Let seek
     * implementation do it.
     *
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Return whether or not the stream is writeable. Default to false, child
     * class should override this eventually.
     *
     * @return boolean
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Returns whether or not the stream is readable. Default to false, child
     * class should override this eventually.
     *
     * @return boolean
     */
    public function isReadable()
    {
        return false;
    }

    /**
     * Truncate file to size bytes.
     *
     * @param integer $size
     * @throws RuntimeExceptin on error
     */
    public function truncate($size)
    {
        $this->unSupportedOperation(__METHOD__);
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
    public function readLine($size = -1)
    {
        if ($size === null) {
            $size = -1;
        } elseif (!is_integer($size)) {
            throw new InvalidArgumentException(
                'size must be an integer'
            );
        }
        $res = '';
        while ($size < 0 || strlen($res) < $size) {
            $b = $this->read(1);
            if (!$b) {
                break;
            }
            $res .= $b;
            if ("\n" === substr($res, -strlen("\n"))) {
                break;
            }
        }
        return $res;
    }

    /**
     * Return an array of lines from stream. hint can be specified to control
     * the number of lines read: no more lines will be read if the total size
     * of all lines so far exceeds hint. This method may throws RuntimeException
     * if the stream closed as it relly on read method implementation
     *
     * @param integer $hint
     * @return array
     */
    public function readLines($hint = null)
    {
        if ($hint === null || $hint <= 0) {
            $lines = [];
            foreach ($this as $line) {
                $lines[] = $line;
            }
            return $lines;
        }
        $n = 0;
        $lines = [];
        foreach ($this as $line) {
            $lines[] = $line;
            $n += strlen($line);
            if ($n >= $hint) {
                break;
            }
        }
        return $lines;
    }

    /**
     * Write using an iterable to the stream.
     *
     * @param array|\Traversable $lines
     * @return integer Returns the number of bytes written to the stream.
     * @throws \RuntimeException If write method throws it
     */
    public function writeLines($lines)
    {
        $this->checkClosed();
        $written = 0;
        foreach ($lines as $line) {
            $written = $this->write($line);
            if ($written === false) {
                return $written;
            }
        }
        return $written;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return true;
    }

    /**
     * IteratorAggregate implementations. When traversed object of this class
     * typically in foreach, the items you will received is same as when called
     * self#readLine, that will be called repeatedly until EOF. Thanks to Generator
     * to make it possible to implements this.
     *
     * @return \Generator
     */
    public function getIterator()
    {
        $this->checkClosed();
        $line = $this->readLine();
        while ($line) {
            yield $line;
            $line = $this->readLine();
        }
    }

    /**
     *
     */
    public function getMetadata($key = null)
    {
    }

    /**
     * Internal: raise an exception for unsupported operations.
     */
    protected function unsupportedOperation($name)
    {
        throw new RuntimeException(sprintf(
            '"%s.%s() not supported',
            get_class(this),
            $name
        ));
    }

    /**
     * Internal: raise an exception for operations when
     * the object already closed
     */
    protected function checkClosed($msg = null)
    {
        $msg = $msg ?: 'I/O operation on closed file.';
        if ($this->isClosed) {
            throw new RuntimeException($msg);
        }
    }
}
