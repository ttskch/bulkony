<?php

declare(strict_types=1);

namespace Ttskch\Bulkony\Import;

use PHPUnit\Framework\TestCase;
use Ttskch\Bulkony\Fake\RowVisitor\AbortableValidatableRowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\AllOrNothingValidatablePreviewableRowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\AllOrNothingValidatableRowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\PreviewableRowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\RowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\ValidatablePreviewableRowVisitor;
use Ttskch\Bulkony\Fake\RowVisitor\ValidatableRowVisitor;
use Ttskch\Bulkony\Import\Preview\Row;

class ImporterTest extends TestCase
{
    private $importer;
    private $csvFilePath;

    public function setUp(): void
    {
        $this->importer = new Importer();
        $this->csvFilePath = tempnam(sys_get_temp_dir(), md5(uniqid())).'.csv';

        $content = <<<EOS
id,name,email
1,alice,alice@example.com
2,bob,bob@example.com
3,charlie,charlie@example.com
EOS;
        file_put_contents($this->csvFilePath, $content);
    }

    public function testWithRowVisitor()
    {
        // simple row visitor
        $rowVisitor = new RowVisitor();

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS
[import] {"id":"1","name":"alice","email":"alice@example.com"} with ''
[import] {"id":"2","name":"bob","email":"bob@example.com"} with ''
[import] {"id":"3","name":"charlie","email":"charlie@example.com"} with ''
EOS;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    public function testWithValidatableRowVisitor()
    {
        // validatable row visitor
        $rowVisitor = new ValidatableRowVisitor();

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[onError] csv line 3: {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
[import] {"id":"1","name":"alice","email":"alice@example.com"} with 'context'
[import] {"id":"3","name":"charlie","email":"charlie@example.com"} with 'context'
EOS;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    /** @group tmp */
    public function testWithAbortableValidatableRowVisitor()
    {
        // validatable row visitor
        $rowVisitor = new AbortableValidatableRowVisitor();

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[onError] csv line 3: {"id":"2","name":"bob","email":"bob@example.com"}
[import] {"id":"1","name":"alice","email":"alice@example.com"} with 'context'
EOS;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    public function testWithAllOrNothingValidatableRowVisitor()
    {
        // validatable row visitor configured as all-or-nothing
        $rowVisitor = new AllOrNothingValidatableRowVisitor();

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[onError] csv line 3: {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
EOS;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    public function testWithPreviewableRowVisitor()
    {
        // previewable row visitor
        $rowVisitor = new PreviewableRowVisitor();

        $preview = $this->importer->preview($this->csvFilePath, $rowVisitor);

        $previewContent = '';
        /** @var Row $row */
        foreach ($preview as $row) {
            foreach (['id', 'name', 'email'] as $csvHeading) {
                $cell = $row->get($csvHeading);
                $previewContent .= sprintf("%d %s: %s %s (%d errors)\n", $row->getCsvLineNumber(), $cell->getCsvHeading(), $cell->getValue(), $cell->isChanged() ? 'changed' : 'not-changed', $cell->getError() ? count($cell->getError()->getMessages()) : 0);
            }
        }
        $previewContent = trim($previewContent);

        $expectedPreviewContent = <<<EOS1
2 id: 1 not-changed (0 errors)
2 name: alice changed (0 errors)
2 email: alice@example.com changed (0 errors)
3 id: 2 not-changed (0 errors)
3 name: bob changed (0 errors)
3 email: bob@example.com changed (0 errors)
4 id: 3 not-changed (0 errors)
4 name: charlie changed (0 errors)
4 email: charlie@example.com changed (0 errors)
EOS1;

        $this->assertEquals($expectedPreviewContent, $previewContent);

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS2
[import] {"id":"1","name":"alice","email":"alice@example.com"} with ''
[import] {"id":"2","name":"bob","email":"bob@example.com"} with ''
[import] {"id":"3","name":"charlie","email":"charlie@example.com"} with ''
EOS2;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    public function testWithValidatablePreviewableRowVisitor()
    {
        // validatable and previewable row visitor
        $rowVisitor = new ValidatablePreviewableRowVisitor();

        $preview = $this->importer->preview($this->csvFilePath, $rowVisitor);

        ob_start();

        $previewContent = '';
        /** @var Row $row */
        foreach ($preview as $row) {
            foreach (['id', 'name', 'email'] as $csvHeading) {
                $cell = $row->get($csvHeading);
                $previewContent .= sprintf("%d %s: %s %s (%d errors)\n", $row->getCsvLineNumber(), $cell->getCsvHeading(), $cell->getValue(), $cell->isChanged() ? 'changed' : 'not-changed', $cell->getError() ? count($cell->getError()->getMessages()) : 0);
            }
        }
        $previewContent = trim($previewContent);

        $echoed = trim(ob_get_clean());

        $expectedEchoed = <<<EOS1
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
EOS1;

        $this->assertEquals($expectedEchoed, $echoed);

        $expectedPreviewContent = <<<EOS2
2 id: 1 not-changed (0 errors)
2 name: alice changed (0 errors)
2 email: alice@example.com changed (0 errors)
3 id: 2 not-changed (0 errors)
3 name: bob changed (0 errors)
3 email: bob@example.com changed (1 errors)
4 id: 3 not-changed (0 errors)
4 name: charlie changed (0 errors)
4 email: charlie@example.com changed (0 errors)
EOS2;

        $this->assertEquals($expectedPreviewContent, $previewContent);

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS3
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[onError] csv line 3: {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
[import] {"id":"1","name":"alice","email":"alice@example.com"} with 'context'
[import] {"id":"3","name":"charlie","email":"charlie@example.com"} with 'context'
EOS3;
        $this->assertEquals($expectedEchoed, $echoed);
    }

    public function testWithAllOrNothingValidatablePreviewableRowVisitor()
    {
        // validatable and previewable row visitor configured as all-or-nothing
        $rowVisitor = new AllOrNothingValidatablePreviewableRowVisitor();

        $preview = $this->importer->preview($this->csvFilePath, $rowVisitor);

        ob_start();

        $previewContent = '';
        /** @var Row $row */
        foreach ($preview as $row) {
            foreach (['id', 'name', 'email'] as $csvHeading) {
                $cell = $row->get($csvHeading);
                $previewContent .= sprintf("%d %s: %s %s (%d errors)\n", $row->getCsvLineNumber(), $cell->getCsvHeading(), $cell->getValue(), $cell->isChanged() ? 'changed' : 'not-changed', $cell->getError() ? count($cell->getError()->getMessages()) : 0);
            }
        }
        $previewContent = trim($previewContent);

        $echoed = trim(ob_get_clean());

        $expectedEchoed = <<<EOS1
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
EOS1;

        $this->assertEquals($expectedEchoed, $echoed);

        $expectedPreviewContent = <<<EOS2
2 id: 1 not-changed (0 errors)
2 name: alice changed (0 errors)
2 email: alice@example.com changed (0 errors)
3 id: 2 not-changed (0 errors)
3 name: bob changed (0 errors)
3 email: bob@example.com changed (1 errors)
4 id: 3 not-changed (0 errors)
4 name: charlie changed (0 errors)
4 email: charlie@example.com changed (0 errors)
EOS2;

        $this->assertEquals($expectedPreviewContent, $previewContent);

        ob_start();
        $this->importer->import($this->csvFilePath, $rowVisitor);
        $echoed = trim(ob_get_clean());
        $expectedEchoed = <<<EOS3
[validate] {"id":"1","name":"alice","email":"alice@example.com"}
[validate] {"id":"2","name":"bob","email":"bob@example.com"}
[onError] csv line 3: {"id":"2","name":"bob","email":"bob@example.com"}
[validate] {"id":"3","name":"charlie","email":"charlie@example.com"}
EOS3;
        $this->assertEquals($expectedEchoed, $echoed);
    }
}
