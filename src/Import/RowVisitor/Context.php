<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

/**
 * @implements \ArrayAccess<mixed, mixed>
 * @implements \IteratorAggregate<mixed>
 */
class Context implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array<mixed>
     */
    private $container = [];

    /**
     * @see \ArrayAccess::offsetExists()
     *
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * @see \ArrayAccess::offsetGet()
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange] // @todo when bump minimum php version to 8.0 remove this line
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * @see \ArrayAccess::offsetSet()
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * @see \IteratorAggregate::getIterator()
     *
     * @return \Traversable<mixed>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->container);
    }

    /**
     * @see \Countable::count()
     */
    public function count(): int
    {
        return count($this->container);
    }
}
