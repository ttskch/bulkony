<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

use Ttskch\Bulkony\Import\Validation\ErrorList;

interface ValidatableRowVisitorInterface extends RowVisitorInterface
{
    public function validate(array $csvRow, ErrorList $errorList, Context $context): void;

    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void;

    public function getAllOrNothing(): bool;
}
