<?php declare(strict_types=1);

namespace SineFine\Ponymator\Graph\Experimental;

use Ponymator\Parser\Ast\CallNode;
use Ponymator\Parser\Ast\EntityNode;
use Ponymator\Parser\Ast\MemberNode;
use Ponymator\Parser\Ast\ParameterNode;

final class EntityGraphProcessor
{
    /** @var array<string, int> fqn => id */
    private array $entityIds = [];

    /** @var array<string, array<string, int>> entityFqn => memberKey => id */
    private array $memberIds = [];

    /** @var array<string, int> shortcut for built-in type lookup */
    private const BUILTIN_TYPES = [
        'string', 'int', 'float', 'bool', 'array', 'void', 'null',
        'object', 'mixed', 'never', 'true', 'false',
        'self', 'parent', 'static', 'iterable', 'callable',
    ];

    public function __construct(
        private GraphCommand $command,
        private NamespaceResolver $namespaceResolver,
    ) {
    }

    public function processEntity(EntityNode $entity, int $fileId): void
    {
        $fqn = $entity->name;
        $type = $this->mapEntityType($entity->type);

        $namespaceFqn = NamespaceResolver::extractFromFqn($fqn);
        $namespaceId = $namespaceFqn !== null ? $this->namespaceResolver->ensure($namespaceFqn) : null;

        $shortName = NamespaceResolver::extractShortName($fqn);

        $entityId = $this->command->insertEntity(
            fqn: $fqn,
            shortName: $shortName,
            type: $type,
            namespaceId: $namespaceId,
            fileId: $fileId,
            parentClass: !empty($entity->extends) ? $entity->extends[0] : null,
            modifiers: $entity->attributes,
            scalarType: null,
        );
        $this->entityIds[$fqn] = $entityId;
        $this->memberIds[$fqn] = [];

        $this->processStructuralRelationships($entityId, $entity);

        foreach ($entity->members as $member) {
            $this->processMember($fqn, $entityId, $member);
        }
    }

    /**
     * @return array<string, int>
     */
    public function getEntityIds(): array
    {
        return $this->entityIds;
    }

    private function processStructuralRelationships(int $entityId, EntityNode $entity): void
    {
        foreach ($entity->extends as $parent) {
            $this->command->insertRelationship(
                sourceId: $entityId,
                targetId: null,
                targetFqn: $parent,
                type: 'extends',
                sourceMemberId: null,
            );
        }

        foreach ($entity->implements as $interface) {
            $this->command->insertRelationship(
                sourceId: $entityId,
                targetId: null,
                targetFqn: $interface,
                type: 'implements',
                sourceMemberId: null,
            );
        }

        foreach ($entity->traits as $trait) {
            $this->command->insertRelationship(
                sourceId: $entityId,
                targetId: null,
                targetFqn: $trait,
                type: 'uses_trait',
                sourceMemberId: null,
            );
        }
    }

    private function processMember(string $entityFqn, int $entityId, MemberNode $member): void
    {
        $memberType = $this->mapMemberType($member->type);
        $isStatic = in_array('static', $member->attributes, true);
        $isAbstract = in_array('abstract', $member->attributes, true);
        $isFinal = in_array('final', $member->attributes, true);
        $isReadonly = in_array('readonly', $member->attributes, true);

        $memberId = $this->command->insertMember(
            entityId: $entityId,
            name: $member->name,
            memberType: $memberType,
            visibility: $member->visibility,
            isStatic: $isStatic,
            isAbstract: $isAbstract,
            isFinal: $isFinal,
            isReadonly: $isReadonly,
            declaredType: $member->dataType,
            typeNullable: $member->dataType !== null && $this->isNullable($member->dataType),
            defaultValue: $member->value,
            returnType: $member->returnType,
            returnTypeNullable: $member->returnType !== null && $this->isNullable($member->returnType),
        );
        $this->memberIds[$entityFqn][$memberType . ':' . $member->name] = $memberId;

        if ($member->dataType !== null && $memberType === 'property') {
            $this->addTypeRelationships($entityId, $member->dataType, 'property_type', $memberId);
        }

        if ($member->returnType !== null && ($memberType === 'method' || $memberType === 'function')) {
            $this->addTypeRelationships($entityId, $member->returnType, 'return_type', $memberId);
        }

        foreach ($member->parameters as $position => $param) {
            $this->processParameter($memberId, $param, $position);

            if ($param->type !== null) {
                $this->addTypeRelationships($entityId, $param->type, 'param_type', $memberId);
            }
        }

        foreach ($member->creates as $createdClass) {
            $this->command->insertRelationship(
                sourceId: $entityId,
                targetId: null,
                targetFqn: $createdClass,
                type: 'creates_weak',
                sourceMemberId: $memberId,
            );
        }

        foreach ($member->calls as $call) {
            $this->processCall($entityId, $memberId, $call);
        }
    }

    private function processCall(int $entityId, int $memberId, CallNode $call): void
    {
        $relType = $this->callToRelType($call);
        $targetFqn = $call->targetFQCN;

        $this->command->insertRelationship(
            sourceId: $entityId,
            targetId: null,
            targetFqn: $targetFqn !== '' ? $targetFqn : null,
            type: $relType,
            sourceMemberId: $memberId,
        );
    }

    private function processParameter(int $memberId, ParameterNode $param, int $position): void
    {
        $this->command->insertParameter(
            memberId: $memberId,
            name: $param->name,
            declaredType: $param->type,
            typeNullable: $param->type !== null && $this->isNullable($param->type),
            defaultValue: $param->value,
            isVariadic: $param->isVariadic,
            isPassedByReference: $param->byRef,
            position: $position,
        );
    }

    private function mapEntityType(string $psv1Type): string
    {
        return match ($psv1Type) {
            'class', 'file' => 'class',
            'interface' => 'interface',
            'trait' => 'trait',
            'enum' => 'enum',
            default => 'class',
        };
    }

    private function mapMemberType(string $psv1Type): string
    {
        return match ($psv1Type) {
            'property', 'global_variable' => 'property',
            'constant' => 'constant',
            'method', 'function' => 'method',
            'enum_case' => 'case',
            default => 'property',
        };
    }

    private function callToRelType(CallNode $call): string
    {
        $suffix = $call->marker === 'strong' ? '_strong' : '_weak';

        return match ($call->type) {
            CallNode::TYPE_STATIC => 'call_static' . $suffix,
            CallNode::TYPE_DYNAMIC => 'call_dynamic' . $suffix,
            CallNode::TYPE_GLOBAL => 'call_global' . $suffix,
            default => 'call_dynamic' . $suffix,
        };
    }

    private function addTypeRelationships(int $entityId, string $type, string $relType, ?int $memberId): void
    {
        $types = $this->extractClassTypes($type);
        foreach ($types as $classType) {
            $this->command->insertRelationship(
                sourceId: $entityId,
                targetId: null,
                targetFqn: $classType,
                type: $relType,
                sourceMemberId: $memberId,
            );
        }
    }

    /**
     * @return list<string>
     */
    private function extractClassTypes(string $type): array
    {
        $type = ltrim($type, '?');
        $parts = preg_split('/[|&]/', $type);
        if ($parts === false) {
            return [];
        }
        $result = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || in_array(strtolower($part), self::BUILTIN_TYPES, true)) {
                continue;
            }
            if ($part[0] === '\\') {
                $part = substr($part, 1);
            }
            $result[] = $part;
        }
        return $result;
    }

    private function isNullable(string $type): bool
    {
        return str_starts_with($type, '?') || stripos($type, '|null') !== false || stripos($type, 'null|') !== false;
    }
}
