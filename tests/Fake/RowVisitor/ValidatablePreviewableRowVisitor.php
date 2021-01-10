<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;

class ValidatablePreviewableRowVisitor extends ValidatableRowVisitor implements PreviewableRowVisitorInterface
{
    public function preview(array $csvRow, Row $previewRow, Context $context): void
    {
        $previewRow->get('name')->setChanged();
        $previewRow->get('email')->setChanged();
    }
}
