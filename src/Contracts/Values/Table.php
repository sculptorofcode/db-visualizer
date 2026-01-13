<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Values;

/**
 * Contract for a database table definition.
 *
 * Represents metadata about a single table.
 */
interface Table
{
    /**
     * Table name (unescaped).
     */
    public function getName(): string;

    /**
     * Schema or database this table belongs to.
     */
    public function getSchema(): ?string;

    /**
     * All columns in this table.
     *
     * @return array<Column>
     */
    public function getColumns(): array;

    /**
     * All indexes on this table.
     *
     * @return array<Index>
     */
    public function getIndexes(): array;

    /**
     * All foreign key constraints on this table.
     *
     * @return array<ForeignKey>
     */
    public function getForeignKeys(): array;

    /**
     * Table comment/description from database, if available.
     */
    public function getComment(): ?string;

    /**
     * Table type (e.g., 'BASE TABLE', 'VIEW', 'SYSTEM TABLE').
     */
    public function getType(): ?string;
}
