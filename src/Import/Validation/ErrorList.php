<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Validation;

class ErrorList implements \IteratorAggregate
{
    /**
     * @var int
     */
    private $csvLineNumber;

    /**
     * @var array|Error[]
     */
    private $errors = [];

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
        return isset($this->errors[$csvHeading]);
    }

    public function get(string $csvHeading, bool $force = false): ?Error
    {
        if ($this->has($csvHeading)) {
            return $this->errors[$csvHeading];
        }

        if ($force) {
            $error = new Error($csvHeading);
            $this->upsert($error);

            return $error;
        }

        return null;
    }

    public function upsert(Error $error): self
    {
        $this->errors[$error->getCsvHeading()] = $error;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    /**
     * @see \IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }
}
