<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\RowVisitor;

use Ttskch\Bulkony\Import\Validation\ErrorList;

interface ValidatableRowVisitorInterface extends RowVisitorInterface
{
    public const CONTINUE_ON_ERROR = true;
    public const ABORT_ON_ERROR = false;

    /**
     * @param array<string> $csvRow
     */
    public function validate(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void;

    /**
     * @param array<string> $csvRow
     */
    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): bool;
}
