<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Export;

use PHPUnit\Framework\TestCase;
use Ttskch\Bulkony\Fake\RowGenerator\RowGenerator;

class ExporterTest extends TestCase
{
    public function testExport()
    {
        $exporter = new Exporter();
        $rowGenerator = new RowGenerator();
        $csvFilePath = tempnam(sys_get_temp_dir(), md5(uniqid())).'.csv';

        $exporter->export($csvFilePath, $rowGenerator);

        $actualContent = file_get_contents($csvFilePath);

        $this->assertRegExp("/^\xef\xbb\xbf/", $actualContent); // with BOM

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
