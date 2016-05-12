<?php

namespace App\Session;

use Headbanger\MutableMapping;
use Headbanger\HashMap;
use Headbanger\ArrayList;
use App\Session\Backends\SessionBackendInterface;
use function Itertools\to_array;

class Store extends MutableMapping
{
    private $accessed = false;

    private $modified = false;

    protected $backend;

    protected $attributes;

    protected $name;

    /**
     *
     */
    public function __construct($name, SessionBackendInterface $backend, $id = null)
    {
        $this->name = $name;
        $this->backend = $backend;
        $this->setId($id);
        $this->attributes = new HashMap();
    }

    /**
     *
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     *
     */
    public function isAccessed()
    {
        return $this->accessed;
    }

    /**
     *
     */
    public function clear()
    {
        $this->attributes->clear();
    }

    /**
     *
     */
    public function count()
    {
        return count($this->attributes);
    }

    /**
     * Test if this mapping contains an item (key).
     */
    public function contains($item)
    {
        return $this->offsetExists($item);
    }

    /**
     *
     */
    public function pop($key, $default = null)
    {
        if (!$this->accessed) {
            $this->loadFromBackend();
        }
        $this->modified = $this->modified || $this->contains($key);
        return $this->attributes->pop($key, $default);
    }

    /**
     *
     */
    public function popItem()
    {
        throw new \RuntimeException('not allowed to pop item');
    }

    /**
     *
     */
    public function getIterator()
    {
        if (!$this->accessed) {
            $this->loadFromBackend();
        }

        return $this->attributes->getIterator();
    }

    /**
     *
     */
    public function offsetSet($key, $value)
    {
        if (!$this->accessed) {
            $this->loadFromBackend();
        }
        $this->modified = true;
        $this->attributes[$key] = $value;
    }

    /**
     *
     */
    public function offsetUnset($key)
    {
        if (!$this->accessed) {
            $this->loadFromBackend();
        }
        $this->modified = true;
        unset($this->attributes[$key]);
    }

    /**
     *
     */
    public function offsetGet($key)
    {
        if (!$this->accessed) {
            $this->loadFromBackend();
        }
        return $this->attributes[$key];
    }

    /**
     *
     */
    private function loadFromBackend()
    {
        $this->accessed = true;
        $data = $this->backend->read($this->getId());
        if ($data) {
            $data = @unserialize($this->prepareForUnserialize($data));

            if ($data !== false && $data !== null && is_array($data)) {
                $this->attributes->update(new ArrayList($data));
            }
        }
    }

    /**
     *
     */
    protected function prepareForUnserialize($data)
    {
        return $data;
    }

    /**
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     */
    public function setId($id)
    {
        if (! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }

        $this->id = $id;
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    public function isValidId($id)
    {
        return is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id);
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        $string = '';
        $length = 16;
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return sha1(uniqid('', true).$string.microtime(true));
    }

    /**
     *
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate($lifetime = null)
    {
        $this->clear();

        return $this->migrate(true, $lifetime);
    }

    /**
     *
     */
    /**
     * {@inheritdoc}
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        if ($destroy) {
            $this->backend->destroy($this->getId());
        }

        $this->id = $this->generateSessionId();

        return true;
    }

    /**
     * Generate a new session identifier.
     *
     * @param  bool  $destroy
     * @return bool
     */
    public function regenerate($destroy = false)
    {
        return $this->migrate($destroy);
    }

    /**
     *
     */
    public function save()
    {
        $items = to_array($this->attributes->items());
        $this->backend->write($this->getId(), $this->prepareForStorage(serialize($items)));
    }

    /**
     *
     */
    protected function prepareForStorage($items)
    {
        return $items;
    }

    /**
     *  Cleanup old sessions
     */
    public function gc($lifetime)
    {
        $this->backend->gc($lifetime);
    }
}
