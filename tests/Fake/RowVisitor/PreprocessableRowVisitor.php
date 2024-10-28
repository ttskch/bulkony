<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreprocessableRowVisitorInterface;

class PreprocessableRowVisitor extends RowVisitor implements PreprocessableRowVisitorInterface
{
    public function preprocess(array $csvRow, int $csvLineNumber, Context $context): void
    {
        $context['context'] = 'from preprocess';

        echo sprintf("[preprocess] csv line %d: %s\n", $csvLineNumber, json_encode($csvRow, JSON_UNESCAPED_UNICODE));
    }
}
