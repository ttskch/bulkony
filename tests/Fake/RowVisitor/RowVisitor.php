<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\RowVisitorInterface;

class RowVisitor implements RowVisitorInterface
{
    public function import(array $csvRow, Context $context): void
    {
        echo sprintf("[import] %s with '%s'\n", json_encode($csvRow, JSON_UNESCAPED_UNICODE), $context['from_validate_to_preview_and_import']);
    }
}
