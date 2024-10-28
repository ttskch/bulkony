<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

interface PreprocessableRowVisitorInterface extends RowVisitorInterface
{
    /**
     * @param array<string> $csvRow
     */
    public function preprocess(array $csvRow, int $csvLineNumber, Context $context): void;
}
