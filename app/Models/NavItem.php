<?php

declare(strict_types=1);

namespace App\Models;

final class NavItem extends Model
{
    private static ?string $orderColumnCache = null;

    /** sort_order (legacy) or order_index after migration. */
    private static function orderColumn(): string
    {
        if (self::$orderColumnCache !== null) {
            return self::$orderColumnCache;
        }
        try {
            $r = self::pdo()->query("SHOW COLUMNS FROM nav_items LIKE 'order_index'")->fetch();
            self::$orderColumnCache = $r ? 'order_index' : 'sort_order';
        } catch (\Throwable) {
            self::$orderColumnCache = 'sort_order';
        }

        return self::$orderColumnCache;
    }

    /** @return array{links: list<array<string, mixed>>, ctas: list<array<string, mixed>>} */
    public static function publicMenu(): array
    {
        return self::safe(function (): array {
            try {
                $stmt = self::pdo()->query('SELECT * FROM nav_items WHERE is_active = 1 ORDER BY ' . self::orderColumn() . ' ASC, id ASC');
                $rows = $stmt->fetchAll() ?: [];
            } catch (\PDOException $e) {
                $m = $e->getMessage();
                if (!str_contains($m, '1054') && !str_contains($m, 'Unknown column')) {
                    throw $e;
                }
                $stmt = self::pdo()->query('SELECT id, label, url, sort_order, is_active, open_new_tab FROM nav_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
                $rows = $stmt->fetchAll() ?: [];
                $flat = [];
                foreach ($rows as $r) {
                    $flat[] = array_merge($r, ['children' => []]);
                }

                return ['links' => self::dedupeNavTree($flat), 'ctas' => []];
            }
            if ($rows === []) {
                return ['links' => [], 'ctas' => []];
            }
            $hasParent = array_key_exists('parent_id', $rows[0]);
            if (!$hasParent) {
                $flat = [];
                foreach ($rows as $r) {
                    if ((int) ($r['is_button'] ?? 0) === 1) {
                        continue;
                    }
                    $flat[] = array_merge($r, ['children' => []]);
                }

                return ['links' => self::dedupeNavTree($flat), 'ctas' => []];
            }

            $roots = self::nestedRootsFromRows($rows);
            $links = [];
            $ctas = [];
            foreach ($roots as $r) {
                if ((int) ($r['is_button'] ?? 0) === 1) {
                    $ctas[] = $r;
                } else {
                    $links[] = $r;
                }
            }

            return [
                'links' => self::dedupeNavTree($links),
                'ctas' => self::dedupeNavTree($ctas),
            ];
        }, ['links' => [], 'ctas' => []]);
    }

    /**
     * Full tree for admin (all items, including inactive), ordered by order_index.
     *
     * @return list<array<string, mixed>>
     */
    public static function adminMenuTree(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM nav_items ORDER BY ' . self::orderColumn() . ' ASC, id ASC');
            $rows = $stmt->fetchAll() ?: [];
            if ($rows === []) {
                return [];
            }
            if (!array_key_exists('parent_id', $rows[0])) {
                $out = [];
                foreach ($rows as $r) {
                    $out[] = array_merge($r, ['children' => []]);
                }

                return $out;
            }

            return self::nestedRootsFromRows($rows);
        }, []);
    }

    /**
     * Nested JSON from drag-and-drop: [ { "id": 1, "children": [ { "id": 2, "children": [] } ] }, ... ]
     * Updates parent_id + order_index for every row in one transaction.
     *
     * @param list<array<string, mixed>> $tree
     */
    public static function applyOrderTree(array $tree): void
    {
        if (!self::treeDepthOk($tree, 1)) {
            throw new \InvalidArgumentException('Menu depth is limited to two levels (parent and one child row).');
        }
        $flat = self::flattenTreePayload($tree, null);
        self::validateOrderFlat($flat);
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            foreach ($flat as $row) {
                $stmt = $pdo->prepare('UPDATE nav_items SET parent_id = :pid, `' . self::orderColumn() . '` = :oi WHERE id = :id');
                $pid = $row['parent_id'];
                $stmt->bindValue(':pid', $pid, $pid === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
                $stmt->bindValue(':oi', $row['order_index'], \PDO::PARAM_INT);
                $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);
                $stmt->execute();
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @param list<array<string, mixed>> $tree */
    private static function treeDepthOk(array $tree, int $level): bool
    {
        if ($level > 2) {
            return false;
        }
        foreach ($tree as $n) {
            $ch = $n['children'] ?? [];
            if (is_array($ch) && $ch !== [] && !self::treeDepthOk($ch, $level + 1)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $nodes
     * @return list<array{id: int, parent_id: int|null, order_index: int}>
     */
    private static function flattenTreePayload(array $nodes, ?int $parentId): array
    {
        $out = [];
        foreach (array_values($nodes) as $i => $node) {
            $id = (int) ($node['id'] ?? 0);
            if ($id < 1) {
                throw new \InvalidArgumentException('Invalid menu id in tree.');
            }
            $out[] = ['id' => $id, 'parent_id' => $parentId, 'order_index' => $i];
            $children = $node['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $out = array_merge($out, self::flattenTreePayload($children, $id));
            }
        }

        return $out;
    }

    /**
     * @param list<array{id: int, parent_id: int|null, order_index: int}> $flat
     */
    private static function validateOrderFlat(array $flat): void
    {
        $dup = [];
        foreach ($flat as $row) {
            $iid = $row['id'];
            if (isset($dup[$iid])) {
                throw new \InvalidArgumentException('Duplicate menu id in tree payload.');
            }
            $dup[$iid] = true;
        }
        $pdo = self::pdo();
        try {
            $stmt = $pdo->query('SELECT id, parent_id, is_button FROM nav_items');
        } catch (\PDOException $e) {
            throw $e;
        }
        $dbRows = $stmt->fetchAll() ?: [];
        $byId = [];
        foreach ($dbRows as $r) {
            $byId[(int) ($r['id'] ?? 0)] = $r;
        }
        $idsDb = array_keys($byId);
        sort($idsDb);
        $idsPayload = array_map(static fn (array $x): int => $x['id'], $flat);
        if (count($idsPayload) !== count(array_unique($idsPayload))) {
            throw new \InvalidArgumentException('Duplicate menu id in flattened tree.');
        }
        $idsPayload = array_values(array_unique($idsPayload));
        sort($idsPayload);
        if ($idsDb !== $idsPayload) {
            throw new \InvalidArgumentException('Tree must include every menu item exactly once.');
        }
        foreach ($flat as $row) {
            $id = $row['id'];
            $pid = $row['parent_id'];
            if ($pid !== null && $pid < 1) {
                throw new \InvalidArgumentException('Invalid parent id.');
            }
            if ($pid !== null && !isset($byId[$pid])) {
                throw new \InvalidArgumentException('Parent not found.');
            }
            if ($pid !== null && $pid === $id) {
                throw new \InvalidArgumentException('Item cannot be its own parent.');
            }
            $meta = $byId[$id] ?? null;
            if ($meta === null) {
                continue;
            }
            if ((int) ($meta['is_button'] ?? 0) === 1 && $pid !== null) {
                throw new \InvalidArgumentException('CTA buttons must stay at the top level.');
            }
            if ($pid !== null) {
                $p = $byId[$pid];
                $pp = $p['parent_id'] ?? null;
                if ($pp !== null && (int) $pp > 0) {
                    throw new \InvalidArgumentException('Nesting is limited to one sub-level.');
                }
            }
            if ($pid !== null) {
                $desc = self::descendantIds($id);
                if (in_array($pid, $desc, true)) {
                    throw new \InvalidArgumentException('Invalid parent (would create a loop).');
                }
            }
        }
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function nestedRootsFromRows(array $rows): array
    {
        $byParent = [];
        foreach ($rows as $r) {
            $pid = $r['parent_id'] ?? null;
            $pk = ($pid === null || (int) $pid === 0) ? 0 : (int) $pid;
            $byParent[$pk][] = $r;
        }
        foreach ($byParent as &$grp) {
            usort($grp, static function (array $a, array $b): int {
                $oa = (int) ($a['order_index'] ?? $a['sort_order'] ?? 0);
                $ob = (int) ($b['order_index'] ?? $b['sort_order'] ?? 0);
                $c = $oa <=> $ob;

                return $c !== 0 ? $c : ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
            });
        }
        unset($grp);

        $nest = static function (int $parentId) use (&$nest, $byParent): array {
            $out = [];
            foreach ($byParent[$parentId] ?? [] as $r) {
                $id = (int) ($r['id'] ?? 0);
                $r['children'] = $nest($id);
                $out[] = $r;
            }

            return $out;
        };

        return $nest(0);
    }

    /**
     * Remove duplicate siblings (same normalized URL + same label), including nested children.
     *
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private static function dedupeNavTree(array $items): array
    {
        $items = self::dedupeSiblings($items);
        foreach ($items as &$r) {
            if (!empty($r['children']) && is_array($r['children'])) {
                $r['children'] = self::dedupeNavTree($r['children']);
            }
        }
        unset($r);

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private static function dedupeSiblings(array $items): array
    {
        $seen = [];
        $out = [];
        foreach ($items as $r) {
            $urlKey = self::normalizeUrlKey(trim((string) ($r['url'] ?? '')));
            $labelKey = mb_strtolower(trim((string) ($r['label'] ?? '')));
            $sig = $urlKey . "\0" . $labelKey;
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $r;
        }

        return $out;
    }

    public static function currentRequestNavKey(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $frag = parse_url($uri, PHP_URL_FRAGMENT);
        $fragStr = is_string($frag) && $frag !== '' ? '#' . $frag : '';

        return self::normalizeUrlKey($path . $fragStr);
    }

    /** Resolved path + fragment key for active state (matches browser pathname + hash). */
    public static function itemNavKey(array $item): string
    {
        $raw = trim((string) ($item['url'] ?? '/'));
        if (preg_match('#^https?://#i', $raw) === 1) {
            return self::normalizeUrlKey($raw);
        }
        $href = \resolve_public_href($raw);
        if (preg_match('#^https?://#i', $href) === 1) {
            return self::normalizeUrlKey($href);
        }
        $path = parse_url($href, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $frag = parse_url($raw, PHP_URL_FRAGMENT);
        if ($frag === null && str_contains($raw, '#')) {
            $frag = (string) parse_url('http://local.apx' . (str_starts_with($raw, '/') ? '' : '/') . $raw, PHP_URL_FRAGMENT);
        }
        $fragStr = is_string($frag) && $frag !== '' ? '#' . $frag : '';

        return self::normalizeUrlKey($path . $fragStr);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            try {
                $stmt = self::pdo()->query('SELECT * FROM nav_items ORDER BY ' . self::orderColumn() . ' ASC, id ASC');

                return $stmt->fetchAll() ?: [];
            } catch (\PDOException $e) {
                $m = $e->getMessage();
                if (!str_contains($m, '1054') && !str_contains($m, 'Unknown column')) {
                    throw $e;
                }
                $stmt = self::pdo()->query('SELECT * FROM nav_items ORDER BY sort_order ASC, id ASC');

                return $stmt->fetchAll() ?: [];
            }
        }, []);
    }

    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM nav_items WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;
        }, null);
    }

    /** @return list<int> */
    public static function descendantIds(int $rootId): array
    {
        if ($rootId < 1) {
            return [];
        }
        $pdo = self::pdo();
        $out = [];
        $queue = [$rootId];
        while ($queue !== []) {
            $pid = array_shift($queue);
            $stmt = $pdo->prepare('SELECT id FROM nav_items WHERE parent_id = :pid');
            $stmt->execute([':pid' => $pid]);
            foreach ($stmt->fetchAll() ?: [] as $r) {
                $cid = (int) ($r['id'] ?? 0);
                if ($cid > 0) {
                    $out[] = $cid;
                    $queue[] = $cid;
                }
            }
        }

        return $out;
    }

    public static function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return '#';
        }
        $lower = strtolower($url);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:') || str_starts_with($lower, 'vbscript:')) {
            return '#';
        }

        return $url;
    }

    public static function sanitizeLabel(string $label): string
    {
        $label = trim(strip_tags($label));

        return mb_substr($label, 0, 120);
    }

    public static function sanitizeIcon(?string $icon): ?string
    {
        if ($icon === null) {
            return null;
        }
        $icon = trim(strip_tags($icon));

        return $icon === '' ? null : mb_substr($icon, 0, 80);
    }

    public static function sanitizeSlug(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }
        $slug = trim(mb_strtolower(strip_tags($slug)));
        $slug = preg_replace('/[^a-z0-9\-_]/', '', $slug) ?? '';

        return $slug === '' ? null : mb_substr($slug, 0, 190);
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO nav_items (parent_id, label, icon, url, slug, `' . self::orderColumn() . '`, is_active, open_new_tab, is_button) VALUES (:parent_id,:label,:icon,:url,:slug,:order_index,:is_active,:open_new_tab,:is_button)');
        $stmt->execute([
            ':parent_id' => self::nullableInt($data['parent_id'] ?? null),
            ':label' => (string) ($data['label'] ?? ''),
            ':icon' => $data['icon'] ?? null,
            ':url' => (string) ($data['url'] ?? ''),
            ':slug' => $data['slug'] ?? null,
            ':order_index' => (int) ($data['order_index'] ?? $data['sort_order'] ?? 0),
            ':is_active' => (int) ($data['is_active'] ?? 1),
            ':open_new_tab' => (int) ($data['open_new_tab'] ?? 0),
            ':is_button' => (int) ($data['is_button'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare('UPDATE nav_items SET parent_id=:parent_id, label=:label, icon=:icon, url=:url, slug=:slug, `' . self::orderColumn() . '`=:order_index, is_active=:is_active, open_new_tab=:open_new_tab, is_button=:is_button WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':parent_id', self::nullableInt($data['parent_id'] ?? null), \PDO::PARAM_INT);
        $stmt->bindValue(':label', (string) ($data['label'] ?? ''));
        $stmt->bindValue(':icon', $data['icon'] ?? null);
        $stmt->bindValue(':url', (string) ($data['url'] ?? ''));
        $stmt->bindValue(':slug', $data['slug'] ?? null);
        $stmt->bindValue(':order_index', (int) ($data['order_index'] ?? $data['sort_order'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':is_active', (int) ($data['is_active'] ?? 1), \PDO::PARAM_INT);
        $stmt->bindValue(':open_new_tab', (int) ($data['open_new_tab'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':is_button', (int) ($data['is_button'] ?? 0), \PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM nav_items WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /** @param list<int|string> $orderedIds */
    public static function reorderFromIdList(array $orderedIds): void
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            foreach (array_values($orderedIds) as $i => $rawId) {
                $id = (int) $rawId;
                if ($id < 1) {
                    continue;
                }
                $stmt = $pdo->prepare('UPDATE nav_items SET `' . self::orderColumn() . '` = :so WHERE id = :id');
                $stmt->execute([':so' => $i, ':id' => $id]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function nullableInt(mixed $v): ?int
    {
        if ($v === null || $v === '' || $v === '0') {
            return null;
        }
        $n = (int) $v;

        return $n < 1 ? null : $n;
    }

    /** Stable key for deduping menu rows (path + fragment; ignores trailing slash noise). */
    public static function normalizeUrlKey(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return strtolower($url);
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = $url;
        }
        $path = '/' . ltrim($path, '/');
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        $frag = parse_url($url, PHP_URL_FRAGMENT);
        $frag = is_string($frag) && $frag !== '' ? '#' . $frag : '';

        return strtolower($path . $frag);
    }
}
