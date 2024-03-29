<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Preview;

use Ttskch\Bulkony\Exception\OutOfBoundsException;

/**
 * @implements \IteratorAggregate<Cell>
 */
class Row implements \IteratorAggregate
{
    /**
     * @var int
     */
    private $csvLineNumber;

    /**
     * @var array<Cell>
     */
    private $cells = [];

    public function __construct(int $csvLineNumber)
    {
        $this->csvLineNumber = $csvLineNumber;
    }

    public function getCsvLineNumber(): int
    {
        return $this->csvLineNumber;
    }

    public function has(string $csvHeading): bool
    {
        return isset($this->cells[$csvHeading]);
    }

    public function get(string $csvHeading, bool $force = false): ?Cell
    {
        if ($this->has($csvHeading)) {
            return $this->cells[$csvHeading];
        }

        if ($force) {
            $cell = new Cell($csvHeading);
            $this->upsert($cell);

            return $cell;
        }

        return null;
    }

    public function remove(string $csvHeading, bool $strict = false): self
    {
        if ($strict && !isset($this->cells[$csvHeading])) {
            throw new OutOfBoundsException();
        }

        unset($this->cells[$csvHeading]);

        return $this;
    }

    public function upsert(Cell $cell): self
    {
        $this->cells[$cell->getCsvHeading()] = $cell;

        return $this;
    }

    public function isChanged(): bool
    {
        foreach ($this->cells as $cell) {
            if ($cell->isChanged()) {
                return true;
            }
        }

        return false;
    }

    public function errorExists(): bool
    {
        foreach ($this->cells as $cell) {
            if ($cell->getError()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @see \IteratorAggregate::getIterator()
     *
     * @return \Traversable<Cell>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->cells);
    }
}
