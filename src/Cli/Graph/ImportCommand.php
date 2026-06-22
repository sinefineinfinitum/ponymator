<?php declare(strict_types=1);

namespace SineFine\Ponymator\Cli\Graph;

use SineFine\Ponymator\Cli\Command;
use SineFine\Ponymator\Cli\Error\ExitCode;
use SineFine\Ponymator\Config;
use SineFine\Ponymator\Db\PDOFactory;
use SineFine\Ponymator\Filesystem\FileFinder;
use SineFine\Ponymator\Graph\Experimental\GraphCommand;
use SineFine\Ponymator\Graph\Experimental\GraphQuery;
use SineFine\Ponymator\Graph\Experimental\Psv1ToGraphImporter;
use SineFine\Ponymator\Graph\Experimental\Schema;
use Throwable;

class ImportCommand
{
    public function execute(Command $cmd, Config $config): void
    {
        $factory = new PDOFactory($cmd, $config);
        $pdo = $factory->connect();

        Schema::create($pdo);

        $targetDir = $config->getTargetAbsolute();
        $finder = new FileFinder();
        $psv1Files = $finder->find($targetDir, ['psv1']);

        if (empty($psv1Files)) {
            fwrite(STDERR, "Error: No .psv1 files found in target directory: $targetDir\n");
            exit(ExitCode::SOURCE_NOT_FOUND);
        }

        $command = new GraphCommand($pdo);
        $query = new GraphQuery($pdo);
        $builder = new Psv1ToGraphImporter($command, $query);

        try {
            $builder->buildFromFiles($psv1Files, $targetDir);
        } catch (Throwable $e) {
            fwrite(STDERR, "Error: Import failed: " . $e->getMessage() . "\n");
            exit(ExitCode::GENERIC_ERROR);
        }

        $entityCount = $query->countEntities();
        $relCount = $query->countRelationships();

        echo "Graph import complete: $entityCount entities, $relCount relationships imported into " . $factory->resolvePath() . "\n";
    }

}
