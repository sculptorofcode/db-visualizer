<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers;

use Sculptor\DbVisualizer\Contracts\Services\Renderer;
use Sculptor\DbVisualizer\Contracts\Values\Column;
use Sculptor\DbVisualizer\Contracts\Values\ForeignKey;
use Sculptor\DbVisualizer\Contracts\Values\Index;
use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Contracts\Values\Table;

/**
 * JSON Renderer for schema visualization.
 *
 * Converts a Schema value object to JSON, escaping all identifiers appropriately.
 * Produces deterministic, ordered output for consistent serialization.
 *
 * All identifiers (table names, column names, index names, etc.) are properly
 * escaped via json_encode(), preventing injection attacks.
 *
 * Output structure:
 * {
 *   "schema": {
 *     "name": "...",
 *     "engine": "mysql",
 *     "tables": [
 *       {
 *         "name": "...",
 *         "schema": "...",
 *         "type": "BASE TABLE",
 *         "comment": "...",
 *         "columns": [...],
 *         "indexes": [...],
 *         "foreignKeys": [...]
 *       }
 *     ]
 *   }
 * }
 */
final class JSONRenderer implements Renderer
{
    /**
     * JSON encoding flags for consistent output.
     */
    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'json';
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType(): string
    {
        return 'application/json';
    }

    /**
     * {@inheritDoc}
     */
    public function render(Schema $schema): string
    {
        $output = [
            'schema' => $this->renderSchema($schema),
        ];

        $json = json_encode($output, self::JSON_FLAGS);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode schema to JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Render a Schema object to an array structure.
     *
     * @param Schema $schema
     *
     * @return array<string, mixed>
     */
    private function renderSchema(Schema $schema): array
    {
        $tables = array_map(
            fn(Table $table) => $this->renderTable($table),
            $schema->getTables()
        );

        // Sort tables by name for deterministic output
        usort($tables, fn($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'name' => $schema->getName(),
            'engine' => $schema->getEngine(),
            'tables' => $tables,
        ];
    }

    /**
     * Render a Table object to an array structure.
     *
     * @param Table $table
     *
     * @return array<string, mixed>
     */
    private function renderTable(Table $table): array
    {
        $columns = array_map(
            fn(Column $col) => $this->renderColumn($col),
            $table->getColumns()
        );

        $indexes = array_map(
            fn(Index $idx) => $this->renderIndex($idx),
            $table->getIndexes()
        );

        // Sort indexes by name for deterministic output
        usort($indexes, fn($a, $b) => strcmp($a['name'], $b['name']));

        $foreignKeys = array_map(
            fn(ForeignKey $fk) => $this->renderForeignKey($fk),
            $table->getForeignKeys()
        );

        // Sort foreign keys by name for deterministic output
        usort($foreignKeys, fn($a, $b) => strcmp($a['name'], $b['name']));

        return array_filter([
            'name' => $table->getName(),
            'schema' => $table->getSchema(),
            'type' => $table->getType(),
            'comment' => $table->getComment(),
            'columns' => $columns,
            'indexes' => $indexes,
            'foreignKeys' => $foreignKeys,
        ]);
    }

    /**
     * Render a Column object to an array structure.
     *
     * @param Column $column
     *
     * @return array<string, mixed>
     */
    private function renderColumn(Column $column): array
    {
        return array_filter([
            'name' => $column->getName(),
            'type' => $column->getType(),
            'nullable' => $column->isNullable(),
            'default' => $column->getDefault(),
            'autoIncrement' => $column->isAutoIncrement() ? true : null,
            'maxLength' => $column->getMaxLength(),
            'comment' => $column->getComment(),
        ]);
    }

    /**
     * Render an Index object to an array structure.
     *
     * @param Index $index
     *
     * @return array<string, mixed>
     */
    private function renderIndex(Index $index): array
    {
        return array_filter([
            'name' => $index->getName(),
            'columns' => $index->getColumnNames(),
            'unique' => $index->isUnique() ? true : null,
            'primary' => $index->isPrimary() ? true : null,
        ]);
    }

    /**
     * Render a ForeignKey object to an array structure.
     *
     * @param ForeignKey $foreignKey
     *
     * @return array<string, mixed>
     */
    private function renderForeignKey(ForeignKey $foreignKey): array
    {
        return array_filter([
            'name' => $foreignKey->getName(),
            'localColumns' => $foreignKey->getLocalColumns(),
            'referencedTable' => $foreignKey->getReferencedTable(),
            'referencedColumns' => $foreignKey->getReferencedColumns(),
            'onDelete' => $foreignKey->getOnDelete(),
            'onUpdate' => $foreignKey->getOnUpdate(),
        ]);
    }
}
