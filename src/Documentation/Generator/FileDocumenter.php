<?php declare(strict_types=1);

namespace SineFine\Ponymator\Documentation\Generator;

use PhpParser\Node;
use SineFine\Ponymator\Analyzer\CombinedAnalysisResult;
use SineFine\Ponymator\Analyzer\CombinedAnalyzer;
use SineFine\Ponymator\Analyzer\FileExtractor;
use SineFine\Ponymator\Analyzer\Link\CrossReferenceContext;
use SineFine\Ponymator\Analyzer\Parser;
use SineFine\Ponymator\Documentation\Renderer\EntityRendererInterface;
use SineFine\Ponymator\Documentation\Renderer\FileRenderer;
use SineFine\Ponymator\Filesystem\PathResolver;

final class FileDocumenter
{
    private ?CrossReferenceContext $context = null;

    /**
     * @param Parser                    $parser
     * @param CombinedAnalyzer          $combinedAnalyzer
     * @param FileExtractor             $fileExtractor
     * @param EntityRendererInterface[] $renderers
     * @param FileRenderer              $fileRenderer
     * @param PathResolver              $pathResolver
     */
    public function __construct(
        private Parser $parser,
        private CombinedAnalyzer $combinedAnalyzer,
        private FileExtractor $fileExtractor,
        private array $renderers,
        private FileRenderer $fileRenderer,
        private PathResolver $pathResolver,
    ) {
    }

    public function document(string $sourcePath, string $relativePath): string
    {
        $ast = $this->parser->parseFile($sourcePath);
        $analysis = $this->combinedAnalyzer->analyze($ast);
        $entities = $analysis->getEntities();

        if (!empty($entities)) {
            return $this->renderEntities($analysis, $entities, $relativePath);
        }

        return $this->renderFileGlobals($ast, $relativePath);
    }

    /**
     * @param CombinedAnalysisResult           $analysis
     * @param array<int, array<string, mixed>> $entities
     * @param string                           $relativePath
     */
    private function renderEntities(CombinedAnalysisResult $analysis, array $entities, string $relativePath): string
    {
        $content = '';
        $currentDocPath = $this->pathResolver->docRelativePath($relativePath);

        $linker = $this->context !== null
            ? new DocLinker($this->context->getFqnToDocPath(), $this->pathResolver)
            : null;

        $dependencies = $linker?->mapToLinks(
            $analysis->getDependencies(),
            $currentDocPath
        ) ?? [];

        $typeLinkResolver = $linker !== null
            ? fn(string $fqn): ?string => $linker->resolveTypeLink($fqn, $currentDocPath)
            : fn(string $fqn): ?string => null;

        $creations = $analysis->getCreations();

        foreach ($entities as $entity) {
            $usedBy = $this->context?->getIndex()->getUsedBy(ltrim($entity['fqn'], '\\')) ?? [];
            $usedByLinks = $linker?->mapToLinks($usedBy, $currentDocPath) ?? [];

            $entityCreates = $creations[$entity['fqn']] ?? [];

            $crossRef = new CrossReference(
                $dependencies,
                $usedByLinks,
                $typeLinkResolver,
                $entityCreates
            );

            $content .= $this->renderEntityByType($entity, $crossRef);
        }

        return $content;
    }

    /**
     * @param array<int, Node> $ast
     */
    private function renderFileGlobals(array $ast, string $relativePath): string
    {
        return $this->fileRenderer->renderFile(
            $relativePath,
            $this->fileExtractor->extractFunctions($ast),
            $this->fileExtractor->extractGlobals($ast),
            $this->fileExtractor->extractConstants($ast),
        );
    }

    public function setContext(CrossReferenceContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function renderEntityByType(array $entity, CrossReference $crossRefs): string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($entity)) {
                return $renderer->renderEntity($entity, $crossRefs);
            }
        }
        return '';
    }
}
