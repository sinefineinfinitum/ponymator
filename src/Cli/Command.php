<?php declare(strict_types=1);

namespace SineFine\Ponymator\Cli;

use PDO;
use PDOException;
use SineFine\Ponymator\Cli\Error\ConfigException;
use SineFine\Ponymator\Cli\Error\ExitCode;
use SineFine\Ponymator\Config;

final class Command
{
    public function __construct(
        public string $group,
        public ?string $subcommand,
        public array $positionalArgs,
        public ?string $configPath,
        public string $output,
        public ?string $dbPath,
        public ?int $depth,
        public bool $helpRequested,
        public bool $isDiff = false,
    ) {
    }

    /**
     * Resolve the database path from --db-path or config.
     * Exits with WRONG_USAGE if neither is available.
     */
    public function resolveDbPath(bool $tryConfig = true): string
    {
        if ($this->dbPath !== null) {
            return $this->dbPath;
        }

        if ($tryConfig) {
            try {
                $config = new Config($this->configPath);
                $dbPath = $config->getDbPath();
                if ($dbPath !== null) {
                    return $dbPath;
                }
            } catch (ConfigException $e) {
                fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
                exit(ExitCode::CONFIG_ERROR);
            }
        }

        fwrite(STDERR, "Error: --db-path is required and no dbPath found in config\n");
        exit(ExitCode::WRONG_USAGE);
    }

    /**
     * Open a SQLite database at the given path.
     * Exits with DATA_ERROR on failure.
     */
    public static function openDb(string $dbPath): PDO
    {
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            fwrite(STDERR, "Error: Cannot open database: " . $e->getMessage() . "\n");
            exit(ExitCode::DATA_ERROR);
        }

        return $pdo;
    }
}
