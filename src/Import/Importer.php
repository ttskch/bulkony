<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import;

use League\Csv\AbstractCsv;
use League\Csv\Reader;
use Ttskch\Bulkony\Exception\RuntimeException;
use Ttskch\Bulkony\Import\Preview\Cell;
use Ttskch\Bulkony\Import\Preview\Preview;
use Ttskch\Bulkony\Import\Preview\Row;
use Ttskch\Bulkony\Import\Reader\NonUniqueHeaderTolerantReader;
use Ttskch\Bulkony\Import\RowVisitor\Context;
use Ttskch\Bulkony\Import\RowVisitor\PreprocessableRowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\PreviewableRowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\RowVisitorInterface;
use Ttskch\Bulkony\Import\RowVisitor\ValidatableRowVisitorInterface;
use Ttskch\Bulkony\Import\Validation\Error;
use Ttskch\Bulkony\Import\Validation\ErrorList;
use Ttskch\Bulkony\Import\Validation\ErrorListCollection;

class Importer
{
    private Context $context;
    private ErrorListCollection $errorListCollection;

    public function __construct()
    {
        $this->context = new Context();
        $this->errorListCollection = new ErrorListCollection();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getErrorListCollection(): ErrorListCollection
    {
        return $this->errorListCollection;
    }

    public function import(string $csvFilePath, RowVisitorInterface $rowVisitor, string $encoding = 'UTF-8', int $headerOffset = 0, bool $nonUniqueHeader = false): void
    {
        $csvReader = $this->getCsvReader($csvFilePath, $encoding, $headerOffset, $nonUniqueHeader);
        /** @var \Iterator<array<string>> $csvRows */
        $csvRows = $csvReader->getRecords();

        if ($rowVisitor instanceof PreprocessableRowVisitorInterface) {
            foreach ($csvRows as $i => $csvRow) {
                $rowVisitor->preprocess($csvRow, $i + 1, $this->context);
            }
        }

        foreach ($csvRows as $i => $csvRow) {
            if ($rowVisitor instanceof ValidatableRowVisitorInterface) {
                $errorList = new ErrorList($i + 1);
                $rowVisitor->validate($csvRow, $i + 1, $errorList, $this->context);

                if (!$errorList->isEmpty()) {
                    $continue = $rowVisitor->onError($csvRow, $i + 1, $errorList, $this->context);
                    $this->errorListCollection->upsert($errorList);
                    if (!$continue) {
                        break;
                    }
                } else {
                    $rowVisitor->import($csvRow, $i + 1, $this->context);
                }
            } else {
                $rowVisitor->import($csvRow, $i + 1, $this->context);
            }
        }
    }

    public function preview(string $csvFilePath, PreviewableRowVisitorInterface $rowVisitor, string $encoding = 'UTF-8', int $headerOffset = 0, bool $nonUniqueHeader = false): Preview
    {
        $csvReader = $this->getCsvReader($csvFilePath, $encoding, $headerOffset, $nonUniqueHeader);
        /** @var \Iterator<array<string>> $csvRows */
        $csvRows = $csvReader->getRecords();

        if ($rowVisitor instanceof PreprocessableRowVisitorInterface) {
            foreach ($csvRows as $i => $csvRow) {
                $rowVisitor->preprocess($csvRow, $i + 1, $this->context);
            }
        }

        /** @var \Generator $previewRows */
        $previewRows = call_user_func(function () use ($csvRows, $rowVisitor) {
            foreach ($csvRows as $i => $csvRow) {
                $previewRow = new Row($i + 1);
                foreach ($csvRow as $csvHeading => $value) {
                    $previewRow->upsert(new Cell($csvHeading, $value));
                }

                if ($rowVisitor instanceof ValidatableRowVisitorInterface) {
                    $errorList = new ErrorList($i + 1);
                    $rowVisitor->validate($csvRow, $i + 1, $errorList, $this->context);
                    /** @var Error $error */
                    foreach ($errorList as $error) {
                        /** @var Cell $cell */
                        $cell = $previewRow->get($error->getCsvHeading(), true);
                        $cell->setError($error);
                    }
                }

                $rowVisitor->preview($csvRow, $i + 1, $previewRow, $this->context);

                yield $previewRow;
            }
        });

        return new Preview($previewRows, $csvReader->count());
    }

    /**
     * @return Reader<array<string>>
     */
    private function getCsvReader(string $csvFilePath, string $encoding, int $headerOffset, bool $nonUniqueHeader): Reader
    {
        if ('UTF-8' !== $encoding) {
            $content = file_get_contents($csvFilePath);

            if (false === $content) {
                throw new RuntimeException();
            }

            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($csvFilePath, $content);
        }

        /** @see AbstractCsv::$is_input_bom_included is false by default, so BOM will be skipped automatically */
        $csv = $nonUniqueHeader ? NonUniqueHeaderTolerantReader::createFromPath($csvFilePath) : Reader::createFromPath($csvFilePath);
        $csv->setHeaderOffset($headerOffset);

        return $csv;
    }
}
