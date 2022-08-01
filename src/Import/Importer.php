<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import;

use League\Csv\AbstractCsv;
use League\Csv\Reader;
use Ttskch\Bulkony\Import\Preview\Cell;
use Ttskch\Bulkony\Import\Preview\Preview;
use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\Reader\NonUniqueHeaderTolerantReader;
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

    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getErrorListCollection(): ErrorListCollection
    {
        return $this->errorListCollection;
    }

    public function import(string $csvFilePath, RowVisitorInterface $rowVisitor, string $encoding = 'UTF-8', int $headerOffset = 0, bool $nonUniqueHeader = false): void
    {
        $csvReader = $this->getCsvReader($csvFilePath, $encoding, $headerOffset, $nonUniqueHeader);
        $csvRows = $csvReader->getRecords();

        $context = new Context();

        foreach ($csvRows as $i => $csvRow) {
            if ($rowVisitor instanceof ValidatableRowVisitorInterface) {
                $errorList = new ErrorList($i + 1);
                $rowVisitor->validate($csvRow, $i + 1, $errorList, $context);

                if (!$errorList->isEmpty()) {
                    $continue = $rowVisitor->onError($csvRow, $i + 1, $errorList, $context);
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

    public function preview(string $csvFilePath, PreviewableRowVisitorInterface $rowVisitor, string $encoding = 'UTF-8', int $headerOffset = 0, bool $nonUniqueHeader = false): Preview
    {
        $csvReader = $this->getCsvReader($csvFilePath, $encoding, $headerOffset, $nonUniqueHeader);
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
                    $rowVisitor->validate($csvRow, $i + 1, $errorList, $context);
                    /** @var Error $error */
                    foreach ($errorList as $error) {
                        $previewRow->get($error->getCsvHeading(), true)->setError($error);
                    }
                }

                $rowVisitor->preview($csvRow, $i + 1, $previewRow, $context);

                yield $previewRow;
            }
        });

        return new Preview($previewRows, $csvReader->count());
    }

    private function getCsvReader(string $csvFilePath, string $encoding, int $headerOffset, bool $nonUniqueHeader): Reader
    {
        if ('UTF-8' !== $encoding) {
            $content = file_get_contents($csvFilePath);
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($csvFilePath, $content);
        }

        /** @see AbstractCsv::$is_input_bom_included is false by default, so BOM will be skipped automatically */
        $csv = $nonUniqueHeader ? NonUniqueHeaderTolerantReader::createFromPath($csvFilePath) : Reader::createFromPath($csvFilePath);
        $csv->setHeaderOffset($headerOffset);

        return $csv;
    }
}
