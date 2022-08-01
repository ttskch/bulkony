<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Reader;

use League\Csv\Reader;

class NonUniqueHeaderTolerantReader extends Reader
{
    public function getHeader(): array
    {
        parent::getHeader();

        for ($i = count($this->header) - 1; $i >= 0; --$i) {
            $count = array_count_values($this->header)[$this->header[$i]];
            if ($count > 1) {
                $this->header[$i] .= "_{$count}";
            }
        }

        return $this->header;
    }
}
