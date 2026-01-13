<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Values\Implementations;

use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Contracts\Values\Table;

/**
 * Concrete implementation of Schema contract.
 *
 * Value object; immutable once constructed.
 * Provides convenient table lookup and filtering methods.
 */
final class SchemaImplementation implements Schema
{
    /**
     * Internal map of table name -> table for O(1) lookup.
     *
     * @var array<string, Table>
     */
    private readonly array $tableMap;

    /**
     * @param string $name Database/schema name
     * @param string $engine Database engine name (mysql, pgsql, sqlite, etc)
     * @param array<Table> $tables All tables in schema
     */
    public function __construct(
        private readonly string $name,
        private readonly string $engine,
        private readonly array $tables,
    ) {
        $this->tableMap = $this->buildTableMap();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEngine(): string
    {
        return $this->engine;
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getTable(string $name): ?Table
    {
        return $this->tableMap[$name] ?? null;
    }

    public function getTablesWithForeignKeys(): array
    {
        return array_filter(
            $this->tables,
            fn(Table $table) => count($table->getForeignKeys()) > 0
        );
    }

    /**
     * Build internal map for O(1) table lookup by name.
     *
     * @return array<string, Table>
     */
    private function buildTableMap(): array
    {
        $map = [];
        foreach ($this->tables as $table) {
            $map[$table->getName()] = $table;
        }
        return $map;
    }
}
