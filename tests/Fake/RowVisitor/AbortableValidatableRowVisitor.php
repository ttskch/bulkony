<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\ErrorList;

class AbortableValidatableRowVisitor extends ValidatableRowVisitor
{
    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): bool
    {
        echo sprintf("[onError] csv line %d: %s\n", $errorList->getCsvLineNumber(), json_encode($csvRow, JSON_UNESCAPED_UNICODE));

        return ValidatableRowVisitorInterface::ABORT_ON_ERROR;
    }
}
