<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Values;

/**
 * Contract for a foreign key constraint.
 *
 * Represents a relationship between two tables.
 */
interface ForeignKey
{
    /**
     * Constraint name (unescaped).
     */
    public function getName(): string;

    /**
     * Local column(s) that participate in the foreign key.
     *
     * @return array<string>
     */
    public function getLocalColumns(): array;

    /**
     * Referenced table name (unescaped).
     */
    public function getReferencedTable(): string;

    /**
     * Referenced column(s) in the foreign table.
     *
     * @return array<string>
     */
    public function getReferencedColumns(): array;

    /**
     * ON DELETE action (e.g., 'CASCADE', 'RESTRICT', 'SET NULL').
     */
    public function getOnDelete(): ?string;

    /**
     * ON UPDATE action (e.g., 'CASCADE', 'RESTRICT', 'SET NULL').
     */
    public function getOnUpdate(): ?string;
}
