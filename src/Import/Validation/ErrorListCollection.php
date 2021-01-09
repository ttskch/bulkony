<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Validation;

class ErrorListCollection implements \IteratorAggregate
{
    /**
     * @var array|ErrorList[]
     */
    private $errorLists = [];

    public function has(int $csvLineNumber): bool
    {
        return isset($this->errorLists[$csvLineNumber]);
    }

    public function get(int $csvLineNumber, bool $force = false): ?ErrorList
    {
        if ($this->has($csvLineNumber)) {
            return $this->errorLists[$csvLineNumber];
        }

        if ($force) {
            $errorList = new ErrorList($csvLineNumber);
            $this->upsert($errorList);

            return $errorList;
        }

        return null;
    }

    public function upsert(ErrorList $errorList): self
    {
        $this->errorLists[$errorList->getCsvLineNumber()] = $errorList;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->errorLists);
    }

    /**
     * @see \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->errorLists);
    }
}
