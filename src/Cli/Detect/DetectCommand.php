<?php declare(strict_types=1);

namespace SineFine\Ponymator\Cli\Detect;

use PDO;
use SineFine\Ponymator\Cli\Command;
use SineFine\Ponymator\Detector\Pattern\Catalog\Adapter;
use SineFine\Ponymator\Detector\Pattern\Catalog\Builder;
use SineFine\Ponymator\Detector\Pattern\Catalog\Decorator;
use SineFine\Ponymator\Detector\Pattern\Catalog\FactoryMethod;
use SineFine\Ponymator\Detector\Pattern\Catalog\Singleton;
use SineFine\Ponymator\Detector\Pattern\Catalog\Strategy;
use SineFine\Ponymator\Detector\Pattern\Catalog\TemplateMethod;
use SineFine\Ponymator\Detector\Pattern\Engine\PatternRegistry;
use SineFine\Ponymator\Detector\Pattern\Engine\Engine;
use SineFine\Ponymator\Graph\Experimental\GraphQuery;
use Throwable;

final class DetectCommand
{
    /**
     * @throws Throwable
     */
    public function execute(Command $cmd, GraphQuery $query, ?PDO $readOnlyPdo = null): void
    {
        $registry = new PatternRegistry(
            [
            new Adapter(),
            new Builder(),
            new Decorator(),
            new FactoryMethod(),
            new Strategy(),
            new Singleton(),
            new TemplateMethod(),
            ]
        );

        $pdo = $query->getPdo();

        $engine = new Engine($registry, $pdo, $readOnlyPdo);
        $result = $engine->run();

        foreach ($result->errors as $error) {
            fwrite(STDERR, "Warning: $error\n");
        }

        $view = new PatternView($result, $query);

        $renderer = new ConsoleRenderer();
        $renderer->render($view->blocks);
    }
}
