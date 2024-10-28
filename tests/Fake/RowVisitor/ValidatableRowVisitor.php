<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Fake\RowVisitor;

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

        $context['context'] = 'from validation';

        echo sprintf("[validate] csv line %d: %s\n", $errorList->getCsvLineNumber(), json_encode($csvRow, JSON_UNESCAPED_UNICODE));
    }

    public function onError(array $csvRow, int $csvLineNumber, ErrorList $errorList, Context $context): bool
    {
        echo sprintf("[onError] csv line %d: %s\n", $errorList->getCsvLineNumber(), json_encode($csvRow, JSON_UNESCAPED_UNICODE));

        return ValidatableRowVisitorInterface::CONTINUE_ON_ERROR;
    }
}
