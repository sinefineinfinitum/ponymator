<?php declare(strict_types=1);

namespace SineFine\Ponymator\Documentation\Renderer;

use SineFine\Ponymator\Comparator\HashGenerator;

final class InterfaceRenderer implements EntityRendererInterface
{
    public function __construct(
        private MarkdownBuilder $builder,
    ) {
    }

    public function supports(array $entity): bool
    {
        return $entity['type'] === 'interface';
    }

    /**
     * @param array<string, mixed> $entity
     * @param array<string, mixed> $crossRefs
     */
    public function renderEntity(array $entity, array $crossRefs): string
    {
        $content = $this->buildContent($entity, $crossRefs);
        $hash = HashGenerator::shortHash($content);
        $md = $this->builder->frontmatter(
            [
            'type' => 'interface',
            'hash' => $hash,
            ]
        );
        $md .= $content;
        return $md;
    }

    /**
     * @param array<string, mixed> $entity
     * @param array<string, mixed> $crossRefs
     */
    private function buildContent(array $entity, array $crossRefs): string
    {
        $md = "\n";
        $md .= $this->builder->header(1, '`' . $entity['fqn'] . '`');
        $md .= "\n";

        $md .= $this->builder->header(3, 'Head');
        $md .= $this->builder->kvList(
            [
            'Type' => '`interface`',
            ]
        );
        $md .= "\n";

        if (!empty($entity['interfaces'])) {
            $md .= $this->builder->section('Extended', 3, $this->builder->itemList($entity['interfaces']));
        }

        if (!empty($entity['constants'])) {
            $md .= $this->builder->section('Constants', 3, $this->builder->constantsTable($entity['constants']));
        }

        if (!empty($entity['methods'])) {
            $md .= $this->builder->section('Methods', 3, $this->builder->methodsList($entity['methods']));
        }

        if (!empty($crossRefs['usedByLinks'])) {
            $md .= $this->builder->usedBySection($crossRefs['usedByLinks']);
        }

        $dependencies = $crossRefs['dependencies'] ?? [];
        if (!empty($dependencies)) {
            $md .= $this->builder->section('Dependencies', 3, $this->builder->dependenciesList($dependencies));
        }

        return $md;
    }
}
