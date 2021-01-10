<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

class AllOrNothingValidatablePreviewableRowVisitor extends ValidatablePreviewableRowVisitor
{
    public function getAllOrNothing(): bool
    {
        return true;
    }
}
