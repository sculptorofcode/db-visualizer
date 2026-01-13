<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Values;

/**
 * Contract for a complete schema snapshot.
 *
 * Represents all metadata extracted from a database at a point in time.
 * Immutable by contract; reading from a schema must not mutate it.
 */
interface Schema
{
    /**
     * Database or schema name.
     */
    public function getName(): string;

    /**
     * Database engine name (e.g., 'mysql', 'postgresql', 'sqlite').
     */
    public function getEngine(): string;

    /**
     * All tables in this schema.
     *
     * @return array<Table>
     */
    public function getTables(): array;

    /**
     * Retrieve a single table by name, or null if not found.
     */
    public function getTable(string $name): ?Table;

    /**
     * All tables that reference other tables via foreign keys.
     *
     * @return array<Table>
     */
    public function getTablesWithForeignKeys(): array;
}
