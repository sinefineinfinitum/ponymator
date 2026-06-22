<?php declare(strict_types=1);

namespace SineFine\Ponymator\Tests\Unit\Detector\Pattern\Builtin;

use PHPUnit\Framework\TestCase;
use SineFine\Ponymator\Detector\Pattern\Catalog\Builder;

final class BuilderTest extends TestCase
{
    private Builder $pattern;

    protected function setUp(): void
    {
        $this->pattern = new Builder();
    }

    public function testName(): void
    {
        $this->assertSame('builder', $this->pattern->name());
    }

    public function testRoles(): void
    {
        $this->assertSame(['Builder', 'ConcreteBuilder', 'Director'], $this->pattern->roles());
    }

    public function testCandidateSql(): void
    {
        $this->assertNotEmpty($this->pattern->candidateSql());
    }
}
