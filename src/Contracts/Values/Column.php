<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Values;

/**
 * Contract for a database column definition.
 *
 * Represents metadata about a single column in a table.
 * All identifiers are stored unescaped; escaping occurs at render time only.
 */
interface Column
{
    /**
     * Column name (unescaped).
     */
    public function getName(): string;

    /**
     * Column data type (e.g., 'VARCHAR', 'INT', 'TIMESTAMP').
     */
    public function getType(): string;

    /**
     * Whether the column can contain NULL values.
     */
    public function isNullable(): bool;

    /**
     * Default value for the column, or null if no default exists.
     */
    public function getDefault(): ?string;

    /**
     * Whether the column is auto-incrementing.
     */
    public function isAutoIncrement(): bool;

    /**
     * Column comment/description from database, if available.
     */
    public function getComment(): ?string;

    /**
     * Maximum length for string types, or null if not applicable.
     */
    public function getMaxLength(): ?int;
}
