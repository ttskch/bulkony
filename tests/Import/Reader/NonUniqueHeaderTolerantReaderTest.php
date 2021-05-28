<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import\Reader;

use PHPUnit\Framework\TestCase;

class NonUniqueHeaderTolerantReaderTest extends TestCase
{
    /**
     * @dataProvider getHeaderDataProvider
     */
    public function testGetHeader(string $csv, array $expectedHeader)
    {
        $reader = NonUniqueHeaderTolerantReader::createFromString($csv);
        $reader->setHeaderOffset(0);

        $this->assertEquals($expectedHeader, $reader->getHeader());
    }

    public function getHeaderDataProvider()
    {
        return [
            [
                '見出し1,見出し2,見出し1',
                ['見出し1', '見出し2', '見出し1_2'],
            ],
            [
                '見出し1,見出し1,見出し1',
                ['見出し1', '見出し1_2', '見出し1_3'],
            ],
            [
                '見出し1,見出し2,見出し1,見出し2,見出し1',
                ['見出し1', '見出し2', '見出し1_2', '見出し2_2', '見出し1_3'],
            ],
        ];
    }
}
