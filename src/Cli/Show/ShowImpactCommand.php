<?php declare(strict_types=1);

namespace SineFine\Ponymator\Cli\Show;

use SineFine\Ponymator\Cli\Command;
use SineFine\Ponymator\Cli\Error\ExitCode;
use SineFine\Ponymator\Graph\Experimental\GraphQuery;

final class ShowImpactCommand
{
    public function execute(Command $cmd, GraphQuery $query): void
    {
        if (count($cmd->positionalArgs) < 1) {
            fwrite(STDERR, "Error: show impact requires a entity name\n");
            exit(ExitCode::WRONG_USAGE);
        }

        $name = $cmd->positionalArgs[0];
        $maxDepth = $cmd->depth ?? 3;

        $resolver = new EntityResolver();
        $entityId = $resolver->resolve($name, $query);
        $targetFqn = $resolver->lastResolvedFqn();

        $visited = [$entityId => true];
        $queue = [[$entityId, 0]];
        $discovered = [];

        while (!empty($queue)) {
            [$currentId, $currentDepth] = array_shift($queue);

            if ($currentDepth >= $maxDepth) {
                continue;
            }

            $incoming = $query->findRelationshipsByTarget($currentId);

            foreach ($incoming as $rel) {
                $sourceId = (int) $rel['source_id'];

                if (isset($visited[$sourceId])) {
                    continue;
                }

                $visited[$sourceId] = true;
                $distance = $currentDepth + 1;
                $discovered[] = [
                    'id' => $sourceId,
                    'fqn' => $rel['source_fqn'],
                    'distance' => $distance,
                ];

                $queue[] = [$sourceId, $distance];
            }
        }

        $byType = [];
        foreach ($discovered as $item) {
            $entity = $query->findEntity($item['fqn']);
            if ($entity === null) {
                continue;
            }

            $type = $entity['type'];
            $distance = $item['distance'];

            if (!isset($byType[$type])) {
                $byType[$type] = [];
            }
            if (!isset($byType[$type][$distance])) {
                $byType[$type][$distance] = [];
            }
            $byType[$type][$distance][] = $item['fqn'];
        }

        $typeOrder = ['class', 'interface', 'trait', 'enum'];
        $ordered = [];
        foreach ($typeOrder as $type) {
            if (isset($byType[$type])) {
                $ordered[$type] = $byType[$type];
            }
        }
        foreach ($byType as $type => $data) {
            if (!isset($ordered[$type])) {
                $ordered[$type] = $data;
            }
        }

        foreach ($ordered as $type => &$distances) {
            ksort($distances);
            foreach ($distances as &$fqns) {
                sort($fqns);
            }
            unset($fqns);
        }
        unset($distances);

        $totalCount = 0;
        foreach ($ordered as $distances) {
            foreach ($distances as $fqns) {
                $totalCount += count($fqns);
            }
        }

        echo "Impact of changing: " . $targetFqn . "\n";
        echo "Total affected: " . $totalCount . "\n";

        if ($totalCount === 0) {
            echo "\nNo dependent entitys found.\n";
            return;
        }

        $typeLabels = [
            'class' => 'Classes',
            'interface' => 'Interfaces',
            'trait' => 'Traits',
            'enum' => 'Enums',
        ];

        foreach ($ordered as $type => $distances) {
            $count = 0;
            foreach ($distances as $fqns) {
                $count += count($fqns);
            }

            $label = $typeLabels[$type] ?? ucfirst($type) . 's';
            echo "\n" . $label . " (" . $count . "):\n";

            foreach ($distances as $distance => $fqns) {
                foreach ($fqns as $fqn) {
                    echo "  [distance " . $distance . "] " . $fqn . "\n";
                }
            }
        }
    }
}
