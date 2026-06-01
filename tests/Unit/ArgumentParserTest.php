<?php declare(strict_types=1);

namespace SineFine\Ponymator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SineFine\Ponymator\Cli\ArgumentParser;

final class ArgumentParserTest extends TestCase
{
    public function testDefaultModeIsDiff(): void
    {
        $parser = ArgumentParser::parse(['ponymator']);
        $this->assertSame(ArgumentParser::DIFF, $parser->mode);
    }

    public function testFullFlag(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--full']);
        $this->assertSame(ArgumentParser::FULL, $parser->mode);
    }

    public function testDiffFlag(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--diff']);
        $this->assertSame(ArgumentParser::DIFF, $parser->mode);
    }

    public function testLastFlagWins(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--full', '--diff']);
        $this->assertSame(ArgumentParser::DIFF, $parser->mode);
    }

    public function testConfigPath(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--config=myconfig.json']);
        $this->assertSame('myconfig.json', $parser->configPath);
    }

    public function testConfigPathWithFull(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--full', '--config=custom.json']);
        $this->assertSame(ArgumentParser::FULL, $parser->mode);
        $this->assertSame('custom.json', $parser->configPath);
    }

    public function testHelpFlag(): void
    {
        $parser = ArgumentParser::parse(['ponymator', '--help']);
        $this->assertTrue($parser->helpRequested);
    }

    public function testPrintHelpOutput(): void
    {
        $this->expectOutputRegex('/Usage: ponymator/');
        ArgumentParser::printHelp();
    }
}
