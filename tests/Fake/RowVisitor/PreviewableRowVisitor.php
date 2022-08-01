<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Fake\RowVisitor;

use Ttskch\Bulkony\Import\Preview\Cell;
use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;

class PreviewableRowVisitor extends RowVisitor implements PreviewableRowVisitorInterface
{
    public function preview(array $csvRow, int $csvLineNumber, Row $previewRow, Context $context): void
    {
        /** @var Cell $cell */
        $cell = $previewRow->get('name');
        $cell->setChanged();

        /** @var Cell $cell */
        $cell = $previewRow->get('email');
        $cell->setChanged();
    }
}
