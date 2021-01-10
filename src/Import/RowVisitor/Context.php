<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

class Context implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @see \ArrayAccess
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @see \ArrayAccess
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * @see \ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @see \ArrayAccess
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @see \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->container);
    }

    /**
     * @see \Countable
     */
    public function count()
    {
        return count($this->container);
    }
}
