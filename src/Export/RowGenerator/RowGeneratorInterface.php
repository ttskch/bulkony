<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Export\RowGenerator;

interface RowGeneratorInterface
{
    /**
     * @return array output csv rows for headings
     */
    public function getHeadingRows(): array;

    /**
     * @return \iterable iterator of "output csv rows for one data"
     */
    public function getBodyRowsIterator(): iterable;
}
