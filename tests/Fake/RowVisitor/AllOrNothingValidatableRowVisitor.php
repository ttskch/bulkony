<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowVisitor;

class AllOrNothingValidatableRowVisitor extends ValidatableRowVisitor
{
    public function getAllOrNothing(): bool
    {
        return true;
    }
}
