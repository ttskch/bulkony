<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Preview;

use Ttskch\Bulkony\Import\Validation\Error;

class Cell
{
    /**
     * @var string
     */
    private $csvHeading;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var bool
     */
    private $changed = false;

    /**
     * @var Error|null
     */
    private $error;

    /**
     * @param mixed|null $value
     */
    public function __construct(string $csvHeading, $value = null)
    {
        $this->csvHeading = $csvHeading;
        $this->value = $value;
    }

    public function getCsvHeading(): string
    {
        return $this->csvHeading;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed|null $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function isChanged(): bool
    {
        return $this->changed;
    }

    public function setChanged(bool $changed = true): self
    {
        $this->changed = $changed;

        return $this;
    }

    public function getError(): ?Error
    {
        return $this->error;
    }

    public function setError(Error $error): self
    {
        $this->error = $error;

        return $this;
    }
}
