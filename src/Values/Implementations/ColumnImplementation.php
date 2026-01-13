<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Values\Implementations;

use Sculptor\DbVisualizer\Contracts\Values\Column;

/**
 * Concrete implementation of Column contract.
 *
 * Value object; immutable once constructed.
 */
final class ColumnImplementation implements Column
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly bool $nullable,
        private readonly ?string $default,
        private readonly bool $autoIncrement,
        private readonly ?string $comment,
        private readonly ?int $maxLength,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }
}
