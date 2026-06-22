<?php declare(strict_types=1);

namespace SineFine\Ponymator\Tests\Unit\Detector\Pattern\Builtin;

use PHPUnit\Framework\TestCase;
use SineFine\Ponymator\Detector\Pattern\Catalog\Decorator;

final class DecoratorTest extends TestCase
{
    private Decorator $pattern;

    protected function setUp(): void
    {
        $this->pattern = new Decorator();
    }

    public function testName(): void
    {
        $this->assertSame('decorator', $this->pattern->name());
    }

    public function testRoles(): void
    {
        $this->assertSame(['Component', 'Decorator'], $this->pattern->roles());
    }

    public function testCandidateSql(): void
    {
        $this->assertNotEmpty($this->pattern->candidateSql());
    }
}
