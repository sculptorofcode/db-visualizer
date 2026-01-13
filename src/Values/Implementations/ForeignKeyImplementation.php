<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Values\Implementations;

use Sculptor\DbVisualizer\Contracts\Values\ForeignKey;

/**
 * Concrete implementation of ForeignKey contract.
 *
 * Value object; immutable once constructed.
 */
final class ForeignKeyImplementation implements ForeignKey
{
    /**
     * @param string $name Constraint name
     * @param array<string> $localColumns Local column(s) in foreign key
     * @param string $referencedTable Referenced table name
     * @param array<string> $referencedColumns Referenced column(s)
     * @param string|null $onDelete ON DELETE action
     * @param string|null $onUpdate ON UPDATE action
     */
    public function __construct(
        private readonly string $name,
        private readonly array $localColumns,
        private readonly string $referencedTable,
        private readonly array $referencedColumns,
        private readonly ?string $onDelete,
        private readonly ?string $onUpdate,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocalColumns(): array
    {
        return $this->localColumns;
    }

    public function getReferencedTable(): string
    {
        return $this->referencedTable;
    }

    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }

    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }
}
