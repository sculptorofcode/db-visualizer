<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Values\Implementations;

use Sculptor\DbVisualizer\Contracts\Values\Column;
use Sculptor\DbVisualizer\Contracts\Values\ForeignKey;
use Sculptor\DbVisualizer\Contracts\Values\Index;
use Sculptor\DbVisualizer\Contracts\Values\Table;

/**
 * Concrete implementation of Table contract.
 *
 * Value object; immutable once constructed.
 */
final class TableImplementation implements Table
{
    /**
     * @param string $name Table name
     * @param string|null $schema Schema/database name
     * @param array<Column> $columns All columns
     * @param array<Index> $indexes All indexes
     * @param array<ForeignKey> $foreignKeys All foreign keys
     * @param string|null $comment Table comment
     * @param string|null $type Table type (BASE TABLE, VIEW, etc)
     */
    public function __construct(
        private readonly string $name,
        private readonly ?string $schema,
        private readonly array $columns,
        private readonly array $indexes,
        private readonly array $foreignKeys,
        private readonly ?string $comment,
        private readonly ?string $type,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
