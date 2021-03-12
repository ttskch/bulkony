<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import;

use League\Csv\Reader;
use Ttskch\Bulkony\Import\Preview\Cell;
use Ttskch\Bulkony\Import\Preview\Preview;
use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\RowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\Error;
use Ttskch\Bulkony\Import\Validation\ErrorList;
use Ttskch\Bulkony\Import\Validation\ErrorListCollection;

class Importer
{
    /**
     * @var ErrorListCollection
     */
    private $errorListCollection;

    public function __construct()
    {
        $this->errorListCollection = new ErrorListCollection();
    }

    public function getErrorListCollection(): ErrorListCollection
    {
        return $this->errorListCollection;
    }

    public function import(string $csvFilePath, RowVisitorInterface $rowVisitor): void
    {
        $csvRows = $this->getCsvReader($csvFilePath)->getRecords();

        $context = new Context();

        foreach ($csvRows as $i => $csvRow) {
            if ($rowVisitor instanceof ValidatableRowVisitorInterface) {
                $errorList = new ErrorList($i + 1);
                $rowVisitor->validate($csvRow, $errorList, $context);

                if (!$errorList->isEmpty()) {
                    $continue = $rowVisitor->onError($csvRow, $errorList, $context);
                    $this->errorListCollection->upsert($errorList);
                    if (!$continue) {
                        break;
                    }
                } else {
                    $rowVisitor->import($csvRow, $i + 1, $context);
                }
            } else {
                $rowVisitor->import($csvRow, $i + 1, $context);
            }
        }
    }

    public function preview(string $csvFilePath, PreviewableRowVisitorInterface $rowVisitor): Preview
    {
        $csvReader = $this->getCsvReader($csvFilePath);
        $csvRows = $csvReader->getRecords();

        $context = new Context();

        $previewRows = call_user_func(function () use ($csvRows, $rowVisitor, $context) {
            foreach ($csvRows as $i => $csvRow) {
                $previewRow = new Row($i + 1);
                foreach ($csvRow as $csvHeading => $value) {
                    $previewRow->upsert(new Cell($csvHeading, $value));
                }

                if ($rowVisitor instanceof ValidatableRowVisitorInterface) {
                    $errorList = new ErrorList($i + 1);
                    $rowVisitor->validate($csvRow, $errorList, $context);
                    /** @var Error $error */
                    foreach ($errorList as $error) {
                        $previewRow->get($error->getCsvHeading(), true)->setError($error);
                    }
                }

                $rowVisitor->preview($csvRow, $previewRow, $context);

                yield $previewRow;
            }
        });

        return new Preview($previewRows, $csvReader->count());
    }

    private function getCsvReader(string $csvFilePath): Reader
    {
        $content = ltrim(file_get_contents($csvFilePath), "\xef\xbb\xbf"); // remove BOM
        $content = mb_convert_encoding($content, 'UTF-8', 'auto'); // in case of user-created file
        $csv = Reader::createFromString($content);
        $csv->setHeaderOffset(0);

        return $csv;
    }
}
