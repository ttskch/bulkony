<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Preview;

class Preview extends \IteratorIterator
{
    /**
     * @var int
     */
    private $count;

    public function __construct(\Generator $rows, int $count)
    {
        parent::__construct($rows);

        $this->count = $count;
    }

    public function current(): Row
    {
        return parent::current();
    }

    public function count(): int
    {
        return $this->count;
    }
}
