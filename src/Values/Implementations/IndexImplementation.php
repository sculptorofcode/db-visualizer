<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Values\Implementations;

use Sculptor\DbVisualizer\Contracts\Values\Index;

/**
 * Concrete implementation of Index contract.
 *
 * Value object; immutable once constructed.
 */
final class IndexImplementation implements Index
{
    /**
     * @param string $name Index name
     * @param array<string> $columnNames Column names in this index
     * @param bool $unique Whether this is a unique index
     * @param bool $primary Whether this is a primary key
     */
    public function __construct(
        private readonly string $name,
        private readonly array $columnNames,
        private readonly bool $unique,
        private readonly bool $primary,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }
}
