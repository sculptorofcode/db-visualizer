<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Services;

use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Contracts\Values\Table;
use Sculptor\DbVisualizer\Exceptions\SchemaAccessException;

/**
 * Contract for schema introspection.
 *
 * Reads metadata-only queries from the database.
 * Never accesses table data, never mutates the schema.
 *
 * Failure modes:
 * - SchemaAccessException: Introspection query failed (permission denied, schema element missing)
 * - Graceful degradation: Missing features (e.g., no FK support) return empty sets, not errors
 */
interface SchemaIntrospector
{
    /**
     * Get the complete schema snapshot.
     *
     * @param string|null $databaseName Optional database name. If provided, introspects that database instead of the default one.
     * @throws SchemaAccessException if basic schema metadata cannot be accessed
     */
    public function schema(?string $databaseName = null): Schema;

    /**
     * Get a single table by name.
     *
     * @param string|null $databaseName Optional database name. If provided, gets table from that database.
     * @throws SchemaAccessException if table introspection fails
     */
    public function table(string $name, ?string $databaseName = null): ?Table;

    /**
     * Get all table names in the schema.
     *
     * @param string|null $databaseName Optional database name. If provided, lists tables from that database.
     * @return array<string>
     * @throws SchemaAccessException if table list cannot be accessed
     */
    public function tableNames(?string $databaseName = null): array;

    /**
     * Get all tables that have outgoing foreign keys.
     *
     * @return array<Table>
     * @throws SchemaAccessException if foreign key introspection fails
     */
    public function tablesWithForeignKeys(): array;

    /**
     * Check if the schema is empty (no tables).
     */
    public function isEmpty(): bool;

    /**
     * Get engine-specific capabilities and version information.
     *
     * @param \PDO $pdo The PDO connection
     *
     * @return array<string, mixed> May include: version, max_table_name_length, supports_fk, etc.
     */
    public function getCapabilities(\PDO $pdo): array;
}
