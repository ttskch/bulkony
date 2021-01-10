<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Fake\RowGenerator;

use Ttskch\Bulkony\Export\RowGenerator\RowGeneratorInterface;

class RowGenerator implements RowGeneratorInterface
{
    public function getHeadingRows(): array
    {
        return [
            [
                'id',
                'name',
                'email',
            ],
        ];
    }

    public function getBodyRowsIterator(): iterable
    {
        return [
            [
                [1, 'alice', 'alice@example.com'],
            ],
            [
                [2, 'bob', 'bob@example.com'],
            ],
            [
                [3, 'charlie', 'charlie@example.com'],
            ],
        ];
    }
}
