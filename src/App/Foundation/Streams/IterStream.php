<?php

namespace App\Foundation\Streams;

use function Itertools\iter;
use function Itertools\join;

/**
 * The trick behind sending an enormous amount of data to the client on PSR7.
 * This stream yield item from iterator. It generally a Generator.
 * But you can use 'normal' Iterator. The emitter must not call read method,
 * but instead iterate over this instance without holding it.
 */
class IterStream extends BaseStream
{
    protected $iter;

    /**
     *
     */
    public function __construct($iter)
    {
        $this->setStream($iter);
    }

    /**
     *
     */
    public function setStream($iter)
    {
        $this->iter = iter($iter);
    }

    /**
     *
     */
    public function getStream()
    {
        return $this->iter;
    }

    /**
     *
     */
    public function read($size)
    {
        $this->_unsupportedOperation('read()');
    }

    /**
     *
     */
    public function write($data)
    {
        $this->_unsupportedOperation('write()');
    }

    /**
     *
     */
    public function detach()
    {
        $iter = $this->iter;
        $this->iter = null;
        return $iter;
    }

    /**
     *
     */
    public function close()
    {
        if (!$this->isClosed) {
            if (method_exists($this->iter, 'close')) {
                $this->close();
            }
            parent::close();
        }
    }

    /**
     * This cause the data consumed and it may hurt for large data, the emitter
     * should iterate this instance!
     */
    public function getContents()
    {
        return join('', $this);
    }

    /**
     *
     */
    public function getIterator()
    {
        if ($this->iter) {
            return $this->iter;
        } else {
            // else give them empty iterator
            return iter([]);
        }
    }
}
