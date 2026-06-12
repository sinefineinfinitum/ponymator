<?php declare(strict_types=1);

namespace SineFine\Ponymator\Graph\Experimental;

use PDO;

/**
 * @experimental This API is experimental and may change without notice.
 */
final class GraphQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function findNamespaceId(string $fqn): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM namespaces WHERE fqn = :fqn');
        $stmt->execute(['fqn' => $fqn]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !is_array($row) || !isset($row['id'])) {
            return null;
        }
        return (int) $row['id'];
    }

    public function findEntityId(string $fqn): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM entities WHERE fqn = :fqn');
        $stmt->execute(['fqn' => $fqn]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !is_array($row) || !isset($row['id'])) {
            return null;
        }
        return (int) $row['id'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findEntity(string $fqn): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM entities WHERE fqn = :fqn');
        $stmt->execute(['fqn' => $fqn]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !is_array($row)) {
            return null;
        }
        return $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllEntities(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM entities ORDER BY fqn');
        if ($stmt === false) {
            return [];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findEntitiesByNamespace(string $namespaceFqn): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.* FROM entities e
             JOIN namespaces n ON e.namespace_id = n.id
             WHERE n.fqn = :fqn
             ORDER BY e.fqn'
        );
        $stmt->execute(['fqn' => $namespaceFqn]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findMembersByEntity(int $entityId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM members WHERE entity_id = :entity_id ORDER BY member_type, name'
        );
        $stmt->execute(['entity_id' => $entityId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function findMemberId(int $entityId, string $name, string $memberType): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM members WHERE entity_id = :entity_id AND name = :name AND member_type = :member_type'
        );
        $stmt->execute(
            [
            'entity_id' => $entityId,
            'name' => $name,
            'member_type' => $memberType,
            ]
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !is_array($row) || !isset($row['id'])) {
            return null;
        }
        return (int) $row['id'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findParametersByMember(int $memberId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM parameters WHERE member_id = :member_id ORDER BY position'
        );
        $stmt->execute(['member_id' => $memberId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findRelationshipsBySource(int $sourceId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    m.name AS source_member_name,
                    m.member_type AS source_member_type,
                    m.visibility AS source_member_visibility,
                    m.is_static AS source_member_static,
                    s.fqn AS source_fqn,
                    t.fqn AS target_fqn_resolved
             FROM relationships r
             JOIN entities s ON r.source_id = s.id
             LEFT JOIN members m ON m.id = r.source_member_id
             LEFT JOIN entities t ON r.target_id = t.id
             WHERE r.source_id = :source_id
             ORDER BY r.type, COALESCE(t.fqn, r.target_fqn)'
        );
        $stmt->execute(['source_id' => $sourceId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findRelationshipsByTarget(int $targetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    m.name AS source_member_name,
                    m.member_type AS source_member_type,
                    m.visibility AS source_member_visibility,
                    m.is_static AS source_member_static,
                    s.fqn AS source_fqn,
                    t.fqn AS target_fqn_resolved
             FROM relationships r
             JOIN entities s ON r.source_id = s.id
             JOIN entities t ON r.target_id = t.id
             LEFT JOIN members m ON m.id = r.source_member_id
             WHERE r.target_id = :target_id
             ORDER BY r.type, s.fqn'
        );
        $stmt->execute(['target_id' => $targetId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findRelationshipsByType(string $type): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, s.fqn AS source_fqn, t.fqn AS target_fqn_resolved
             FROM relationships r
             JOIN entities s ON r.source_id = s.id
             LEFT JOIN entities t ON r.target_id = t.id
             WHERE r.type = :type
             ORDER BY s.fqn, COALESCE(t.fqn, r.target_fqn)'
        );
        $stmt->execute(['type' => $type]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllRelationships(): array
    {
        $stmt = $this->pdo->query(
            'SELECT r.*, s.fqn AS source_fqn, t.fqn AS target_fqn_resolved
             FROM relationships r
             JOIN entities s ON r.source_id = s.id
             LEFT JOIN entities t ON r.target_id = t.id
             ORDER BY s.fqn, r.type, COALESCE(t.fqn, r.target_fqn)'
        );
        if ($stmt === false) {
            return [];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllNamespaces(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM namespaces ORDER BY fqn');
        if ($stmt === false) {
            return [];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findNamespaceChildren(int $parentId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM namespaces WHERE parent_id = :parent_id ORDER BY fqn'
        );
        $stmt->execute(['parent_id' => $parentId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array{source_ns: string, target_ns: string, rel_type: string, count: int}>
     */
    public function getDomainCoupling(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                sn.fqn AS source_ns,
                tn.fqn AS target_ns,
                r.type AS rel_type,
                COUNT(*) AS count
             FROM relationships r
             JOIN entities se ON r.source_id = se.id
             JOIN entities te ON r.target_id = te.id
             JOIN namespaces sn ON se.namespace_id = sn.id
             JOIN namespaces tn ON te.namespace_id = tn.id
             WHERE sn.fqn != tn.fqn
             GROUP BY sn.fqn, tn.fqn, r.type
             ORDER BY sn.fqn, tn.fqn, r.type'
        );
        if ($stmt === false) {
            return [];
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $typed = [];
        foreach ($result as $row) {
            if (is_array($row) && isset($row['source_ns'], $row['target_ns'], $row['rel_type'], $row['count'])) {
                $typed[] = [
                    'source_ns' => (string) $row['source_ns'],
                    'target_ns' => (string) $row['target_ns'],
                    'rel_type' => (string) $row['rel_type'],
                    'count' => (int) $row['count'],
                ];
            }
        }
        return $typed;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findEntitiesByNamespaceTree(string $namespaceFqn): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.* FROM entities e
             JOIN namespaces n ON e.namespace_id = n.id
             WHERE n.fqn = :fqn OR n.fqn LIKE :prefix
             ORDER BY e.fqn'
        );
        $stmt->execute(
            [
            'fqn' => $namespaceFqn,
            'prefix' => $namespaceFqn . '\\%',
            ]
        );
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findEntitiesByShortName(string $shortName): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM entities WHERE short_name = :name ORDER BY fqn'
        );
        $stmt->execute(['name' => $shortName]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFileById(int $fileId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM files WHERE id = :id');
        $stmt->execute(['id' => $fileId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false || !is_array($row)) {
            return null;
        }
        return $row;
    }

    public function countEntities(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM entities');
        if ($stmt === false) {
            return 0;
        }
        $value = $stmt->fetchColumn();
        return is_numeric($value) ? (int) $value : 0;
    }

    public function countRelationships(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM relationships');
        if ($stmt === false) {
            return 0;
        }
        $value = $stmt->fetchColumn();
        return is_numeric($value) ? (int) $value : 0;
    }

    public function countMembers(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM members');
        if ($stmt === false) {
            return 0;
        }
        $value = $stmt->fetchColumn();
        return is_numeric($value) ? (int) $value : 0;
    }

    public function countNamespaces(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM namespaces');
        if ($stmt === false) {
            return 0;
        }
        $value = $stmt->fetchColumn();
        return is_numeric($value) ? (int) $value : 0;
    }
}
