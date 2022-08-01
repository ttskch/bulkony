<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Validation;

class Error
{
    /**
     * @var string
     */
    private $csvHeading;

    /**
     * @var array<string>
     */
    private $messages;

    /**
     * @param array<string> $messages
     */
    public function __construct(string $csvHeading, array $messages = [])
    {
        $this->csvHeading = $csvHeading;
        $this->messages = $messages;
    }

    public function getCsvHeading(): string
    {
        return $this->csvHeading;
    }

    /**
     * @return array<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessage(string $message): self
    {
        $this->messages[] = $message;

        return $this;
    }
}
