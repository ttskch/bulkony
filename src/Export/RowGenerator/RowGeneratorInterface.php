<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Export\RowGenerator;

interface RowGeneratorInterface
{
    /**
     * @return array<array<string>> output csv rows for headings
     */
    public function getHeadingRows(): array;

    /**
     * @return iterable<array<array<string>>> iterator of "output csv rows for one data"
     */
    public function getBodyRowsIterator(): iterable;
}
