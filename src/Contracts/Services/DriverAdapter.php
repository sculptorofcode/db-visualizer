<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Services;

/**
 * Contract for database-specific introspection adapters.
 *
 * Each supported database engine (MySQL, PostgreSQL, SQLite, etc.) implements this interface.
 * The adapter handles engine-specific metadata query syntax and normalization.
 *
 * Guarantees:
 * - Only metadata queries are executed (INFORMATION_SCHEMA, DESCRIBE, PRAGMA, etc.)
 * - All results are normalized to the shared SchemaIntrospector contract
 * - No engine-specific types leak to the caller
 *
 * Implementations must be testable via dependency injection (PDO passed in constructor).
 */
interface DriverAdapter extends SchemaIntrospector
{
    /**
     * Get the name of the database engine this adapter supports.
     *
     * @return string One of: 'mysql', 'pgsql', 'sqlite', etc.
     */
    public function engineName(): string;

    /**
     * Verify the adapter can execute metadata queries on the given PDO connection.
     *
     * This is a shallow check (can we execute a basic query?), not a deep permission audit.
     *
     * @return bool True if the adapter is usable, false otherwise
     */
    public function isSupported(\PDO $pdo): bool;

    /**
     * Get engine-specific capabilities and version information.
     *
     * @return array<string, mixed> May include: version, max_table_name_length, supports_fk, etc.
     */
    public function getCapabilities(\PDO $pdo): array;
}
