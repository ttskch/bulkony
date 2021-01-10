<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Export;

use League\Csv\Writer;
use Ttskch\Bulkony\Export\RowGenerator\RowGeneratorInterface;

class Exporter
{
    public function export(string $csvFilePath, RowGeneratorInterface $rowGenerator): void
    {
        $content = '';

        $content .= "\xef\xbb\xbf"; // add BOM for Excel

        foreach ($rowGenerator->getHeadingRows() as $headingRow) {
            $content .= $this->rowToString($headingRow);
        }

        foreach ($rowGenerator->getBodyRowsIterator() as $rows) {
            foreach ($rows as $row) {
                $content .= $this->rowToString($row);
            }
        }

        file_put_contents($csvFilePath, $content);
    }


    private function rowToString(array $row): string
    {
        $csv = Writer::createFromString('');
        $csv->insertOne($row);

        return $csv->getContent();
    }
}
