<?php declare(strict_types=1);

namespace SineFine\Ponymator;

use SineFine\Ponymator\Cli\ArgumentParser;
use SineFine\Ponymator\Cli\Command;
use SineFine\Ponymator\Cli\Error\ConfigException;
use SineFine\Ponymator\Cli\Error\ExitCode;
use SineFine\Ponymator\Cli\ExecutionTimer;
use SineFine\Ponymator\Cli\Generate\GenerateCommand;
use SineFine\Ponymator\Cli\Graph\ClearCommand;
use SineFine\Ponymator\Cli\Graph\ImportCommand;
use SineFine\Ponymator\Cli\HelpPrinter;
use SineFine\Ponymator\Cli\Detect\DetectCommand;
use SineFine\Ponymator\Cli\Show\ShowEntityCommand;
use SineFine\Ponymator\Cli\Show\ShowImpactCommand;
use SineFine\Ponymator\Cli\Show\ShowPathCommand;
use SineFine\Ponymator\Db\PDOFactory;
use SineFine\Ponymator\Graph\Experimental\GraphQuery;
use Throwable;

class Ponymator
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        $timer = new ExecutionTimer();
        register_shutdown_function([$timer, 'finish']);

        $cmd = ArgumentParser::parse($_SERVER['argv'] ?? []);

        if ($cmd->helpRequested) {
            $this->printHelp($cmd);
            exit(ExitCode::SUCCESS);
        }

        $config = $this->getConfig($cmd);

        match ($cmd->group) {
            'generate' => (new GenerateCommand())->execute($cmd, $config),
            'graph' => $this->handleGraph($cmd, $config),
            'show' => $this->handleShow($cmd, $config),
            'detect' => $this->handleDetect($cmd, $config),
            default => HelpPrinter::printHelp(),
        };
    }

    private function getConfig(Command $cmd): Config
    {
        try {
            return new Config($cmd->configPath);
        } catch (ConfigException $e) {
            fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
            exit(ExitCode::CONFIG_ERROR);
        }
    }

    private function handleGraph(Command $cmd, Config $config): void
    {
        match ($cmd->subcommand) {
            'import' => (new ImportCommand())->execute($cmd, $config),
            'clear' => (new ClearCommand())->execute($cmd, $config),
            default => HelpPrinter::printGraphHelp(),
        };
    }

    private function handleShow(Command $cmd, Config $config): void
    {
        $factory = new PDOFactory($cmd, $config);
        $pdo = $factory->connect(requireExisting: true);

        $query = new GraphQuery($pdo);

        match ($cmd->subcommand) {
            'entity' => (new ShowEntityCommand())->execute($cmd, $query),
            'impact' => (new ShowImpactCommand())->execute($cmd, $query),
            'path' => (new ShowPathCommand())->execute($cmd, $query),
            default => HelpPrinter::printShowHelp(),
        };
    }

    /**
     * @throws Throwable
     */
    private function handleDetect(Command $cmd, Config $config): void
    {
        $factory = new PDOFactory($cmd, $config);
        $pdo = $factory->connect(requireExisting: true);
        $readOnlyPdo = $factory->connectReadOnly();
        $query = new GraphQuery($pdo);

        (new DetectCommand())->execute($cmd, $query, $readOnlyPdo);
    }

    private function printHelp(Command $cmd): void
    {
        match ($cmd->group) {
            'generate' => HelpPrinter::printGenerateHelp(),
            'graph' => HelpPrinter::printGraphHelp(),
            'show' => HelpPrinter::printShowHelp(),
            'detect' => HelpPrinter::printDetectHelp(),
            default => HelpPrinter::printHelp(),
        };
    }
}
