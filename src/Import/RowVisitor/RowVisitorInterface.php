<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

interface RowVisitorInterface
{
    /**
     * @param array<string> $csvRow
     */
    public function import(array $csvRow, int $csvLineNumber, Context $context): void;
}
