<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

interface RowVisitorInterface
{
    public function import(array $csvRow, int $csvLineNumber, Context $context): void;
}
