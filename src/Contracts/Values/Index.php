<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Values;

/**
 * Contract for a database index definition.
 *
 * Represents metadata about a single index on a table.
 */
interface Index
{
    /**
     * Index name (unescaped).
     */
    public function getName(): string;

    /**
     * Column names that make up this index.
     *
     * @return array<string>
     */
    public function getColumnNames(): array;

    /**
     * Whether this is a unique index.
     */
    public function isUnique(): bool;

    /**
     * Whether this is a primary key index.
     */
    public function isPrimary(): bool;
}
