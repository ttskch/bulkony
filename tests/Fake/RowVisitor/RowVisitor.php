<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\RowVisitorInterface;

class RowVisitor implements RowVisitorInterface
{
    public function import(array $csvRow, int $csvLineNumber, Context $context): void
    {
        assert(is_string($context['context']) || is_null($context['context']));

        echo sprintf("[import] csv line %d: %s with '%s'\n", $csvLineNumber, json_encode($csvRow, JSON_UNESCAPED_UNICODE), strval($context['context']));
    }
}
