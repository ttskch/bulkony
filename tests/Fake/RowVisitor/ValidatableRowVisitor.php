<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\ErrorList;

class ValidatableRowVisitor extends RowVisitor implements ValidatableRowVisitorInterface
{
    public function validate(array $csvRow, ErrorList $errorList, Context $context): void
    {
        if ($csvRow['name'] === 'bob') {
            $errorList->get('email', true)->addMessage('Invalid email address');
        }

        $context['from_validate_to_preview_and_import'] = 'context';

        echo sprintf("[validate] %s\n", json_encode($csvRow, JSON_UNESCAPED_UNICODE));
    }

    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void
    {
        echo sprintf("[onError] csv line %d: %s\n", $csvLineNumber, json_encode($csvRow, JSON_UNESCAPED_UNICODE));
    }

    public function getAllOrNothing(): bool
    {
        return false;
    }
}
