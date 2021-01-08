<?php

declare(strict_types=1);

namespace Ttskch\Bulkony;

use PHPUnit\Framework\TestCase;

class BulkonyTest extends TestCase
{
    /** @var Bulkony */
    protected $bulkony;

    protected function setUp(): void
    {
        $this->bulkony = new Bulkony();
    }

    public function testIsInstanceOfBulkony(): void
    {
        $actual = $this->bulkony;
        $this->assertInstanceOf(Bulkony::class, $actual);
    }
}
