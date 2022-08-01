<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\Error;
use Ttskch\Bulkony\Import\Validation\ErrorList;

class ValidatableRowVisitor extends RowVisitor implements ValidatableRowVisitorInterface
{
    public function validate(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): void
    {
        if ('bob' === $csvRow['name']) {
            /** @var Error $error */
            $error = $errorList->get('email', true);
            $error->addMessage('Invalid email address');
        }

        $context['from_validate_to_preview_and_import'] = 'context';

        echo sprintf("[validate] csv line %d: %s\n", $errorList->getCsvLineNumber(), json_encode($csvRow, JSON_UNESCAPED_UNICODE));
    }

    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): bool
    {
        echo sprintf("[onError] csv line %d: %s\n", $errorList->getCsvLineNumber(), json_encode($csvRow, JSON_UNESCAPED_UNICODE));

        return ValidatableRowVisitorInterface::CONTINUE_ON_ERROR;
    }
}
