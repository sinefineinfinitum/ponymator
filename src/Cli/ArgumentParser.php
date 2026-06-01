<?php declare(strict_types=1);

namespace SineFine\Ponymator\Cli;

final class ArgumentParser
{
    public const FULL = 'full';
    public const DIFF = 'diff';
    private function __construct(
        public string $mode,
        public ?string $configPath,
        public bool $helpRequested,
    ) {
    }

    /**
     * @param string[] $argv
     */
    public static function parse(array $argv): self
    {
        $mode = self::DIFF;
        $configPath = null;
        $helpRequested = false;

        array_shift($argv);

        foreach ($argv as $arg) {
            match (true) {
                $arg === '--full' => $mode = self::FULL,
                $arg === '--diff' => $mode = self::DIFF,
                str_starts_with($arg, '--config=') => $configPath = substr($arg, 9),
                $arg === '--help' => $helpRequested = true,
                default => null,
            };
        }

        return new self($mode, $configPath, $helpRequested);
    }

    public static function printHelp(): void
    {
        echo <<<'HELP'
Usage: ponymator [options]

Options:
  --full              Regenerate all documentation
  --diff              Regenerate only changed files (default)
  --config=<path>     Path to config file (default: .ponymator.json)
  --help              Display this help message

Exit codes:
  0   Success
  1   Generic error (config, parse, runtime)

HELP;
    }
}
