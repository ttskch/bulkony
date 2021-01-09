<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

use Ttskch\Bulkony\Import\Preview\Row;

interface PreviewableRowVisitorInterface extends RowVisitorInterface
{
    public function preview(array $csvRow, Row $previewRow, Context $context): void;
}
