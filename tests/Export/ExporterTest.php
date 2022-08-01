<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Tests\Export;

use PHPUnit\Framework\TestCase;
use Ttskch\Bulkony\Export\Exporter;
use Ttskch\Bulkony\Tests\Fake\RowGenerator\RowGenerator;

class ExporterTest extends TestCase
{
    public function testExport(): void
    {
        $exporter = new Exporter();
        $rowGenerator = new RowGenerator();
        $csvFilePath = tempnam(sys_get_temp_dir(), md5(uniqid())).'.csv';

        $exporter->export($csvFilePath, $rowGenerator);

        /** @var string $actualContent */
        $actualContent = file_get_contents($csvFilePath);

        $this->assertMatchesRegularExpression("/^\xef\xbb\xbf/", $actualContent); // with BOM

        $actualContent = trim(ltrim($actualContent, "\xef\xbb\xbf"));

        $expectedContent = <<<EOS
id,name,email
1,alice,alice@example.com
2,bob,bob@example.com
3,charlie,charlie@example.com
EOS;

        $this->assertEquals($expectedContent, $actualContent);
    }
}
