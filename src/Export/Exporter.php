<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Export;

use League\Csv\Writer;
use Ttskch\Bulkony\Export\RowGenerator\RowGeneratorInterface;

class Exporter
{
    public function export(string $csvFilePath, RowGeneratorInterface $rowGenerator): void
    {
        $fp = fopen($csvFilePath, 'w+');

        fwrite($fp, "\xef\xbb\xbf"); // add BOM for Excel

        foreach ($rowGenerator->getHeadingRows() as $headingRow) {
            fwrite($fp, $this->rowToString($headingRow));
        }

        foreach ($rowGenerator->getBodyRowsIterator() as $rows) {
            foreach ($rows as $row) {
                fwrite($fp, $this->rowToString($row));
            }
        }

        fclose($fp);
    }


    private function rowToString(array $row): string
    {
        $csv = Writer::createFromString('');
        $csv->insertOne($row);

        return $csv->getContent();
    }
}
