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
 * HTML Renderer for schema visualization.
 *
 * Converts a Schema value object to semantic HTML with minimal inline styles.
 * All identifiers and data are properly escaped to prevent XSS attacks.
 * Produces deterministic, server-side rendered output.
 *
 * Security features:
 * - All identifiers escaped via htmlspecialchars(ENT_QUOTES | ENT_HTML5)
 * - No JavaScript (inline or external)
 * - No CSS frameworks
 * - No external assets
 * - Metadata-only rendering
 *
 * Determinism:
 * - Tables sorted alphabetically by name
 * - Columns preserve schema ordinal position
 * - Indexes sorted alphabetically by name
 * - Foreign keys sorted alphabetically by name
 */
final class HTMLRenderer implements Renderer
{
    /**
     * HTML escaping flags: quote style and encoding.
     */
    private const ESCAPE_FLAGS = ENT_QUOTES | ENT_HTML5;

    /**
     * Charset for HTML output.
     */
    private const CHARSET = 'UTF-8';

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'html';
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType(): string
    {
        return 'text/html; charset=' . self::CHARSET;
    }

    /**
     * {@inheritDoc}
     */
    public function render(Schema $schema): string
    {
        $html = $this->renderDocumentHead($schema);
        $html .= $this->renderSchemaHeader($schema);

        if ($schema->getTables()) {
            $tables = $schema->getTables();
            // Sort tables alphabetically for deterministic output
            usort($tables, fn(Table $a, Table $b) => strcmp($a->getName(), $b->getName()));

            foreach ($tables as $table) {
                $html .= $this->renderTableSection($table);
            }
        } else {
            $html .= $this->renderEmptyState();
        }

        $html .= $this->renderDocumentFooter();

        return $html;
    }

    /**
     * Render HTML document head (DOCTYPE, meta, style).
     *
     * @param Schema $schema
     *
     * @return string
     */
    private function renderDocumentHead(Schema $schema): string
    {
        $dbName = $this->escape($schema->getName());

        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schema: HTML . $dbName . </title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", monospace;
            margin: 2rem;
            line-height: 1.6;
            color: #333;
            background: #fafafa;
        }
        h1, h2, h3 {
            margin-top: 2rem;
            color: #222;
        }
        h1 {
            border-bottom: 3px solid #999;
            padding-bottom: 0.5rem;
        }
        h2 {
            border-bottom: 2px solid #ddd;
            padding-bottom: 0.25rem;
        }
        h3 {
            margin-top: 1.5rem;
            font-size: 1.1em;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 1rem 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #222;
        }
        tbody tr:hover {
            background: #f9f9f9;
        }
        code {
            background: #f4f4f4;
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-family: "Courier New", monospace;
            font-size: 0.9em;
        }
        .schema-info {
            background: white;
            border-left: 4px solid #999;
            padding: 1.5rem;
            margin: 2rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .schema-info p {
            margin: 0.5rem 0;
        }
        .table-section {
            margin-bottom: 3rem;
            padding: 1.5rem 0;
            border-bottom: 2px solid #eee;
        }
        .table-section:last-child {
            border-bottom: none;
        }
        .comment {
            color: #666;
            font-style: italic;
            font-size: 0.95em;
            margin: 0.5rem 0;
        }
        .empty {
            color: #999;
            font-style: italic;
            padding: 2rem;
            text-align: center;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .table-meta {
            color: #666;
            font-size: 0.95em;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
HTML;
    }

    /**
     * Render schema header section with database name, engine, and table count.
     *
     * @param Schema $schema
     *
     * @return string
     */
    private function renderSchemaHeader(Schema $schema): string
    {
        $dbName = $this->escape($schema->getName());
        $engine = $this->escape($schema->getEngine());
        $tableCount = count($schema->getTables());

        return <<<HTML
    <div class="schema-info">
        <h1>Database Schema: <code>{$dbName}</code></h1>
        <p><strong>Engine:</strong> <code>{$engine}</code></p>
        <p><strong>Tables:</strong> {$tableCount}</p>
    </div>

HTML;
    }

    /**
     * Render a complete table section (columns, indexes, foreign keys).
     *
     * @param Table $table
     *
     * @return string
     */
    private function renderTableSection(Table $table): string
    {
        $tableName = $this->escape($table->getName());
        $tableType = $this->escape($table->getType() ?? '');
        $comment = $table->getComment() ? $this->escape($table->getComment()) : '';

        $html = <<<HTML
    <div class="table-section">
        <h2><code>{$tableName}</code></h2>

HTML;

        if ($comment) {
            $html .= <<<HTML
        <p class="comment">{$comment}</p>

HTML;
        }

        if ($tableType) {
            $html .= <<<HTML
        <p class="table-meta"><strong>Type:</strong> {$tableType}</p>

HTML;
        }

        // Columns section
        $html .= $this->renderColumnsTable($table->getColumns());

        // Indexes section
        if ($table->getIndexes()) {
            $html .= $this->renderIndexesTable($table->getIndexes());
        }

        // Foreign keys section
        if ($table->getForeignKeys()) {
            $html .= $this->renderForeignKeysTable($table->getForeignKeys());
        }

        $html .= "    </div>\n\n";

        return $html;
    }

    /**
     * Render columns table for a table.
     *
     * @param array<Column> $columns
     *
     * @return string
     */
    private function renderColumnsTable(array $columns): string
    {
        $html = <<<'HTML'
        <h3>Columns</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Nullable</th>
                    <th>Default</th>
                    <th>Auto-Inc</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
HTML;

        foreach ($columns as $column) {
            $name = $this->escape($column->getName());
            $type = $this->escape($column->getType());
            $nullable = $column->isNullable() ? 'YES' : 'NO';
            $default = $column->getDefault() ? $this->escape($column->getDefault()) : '—';
            $autoInc = $column->isAutoIncrement() ? '✓' : '—';
            $comment = $column->getComment() ? $this->escape($column->getComment()) : '—';

            $html .= <<<HTML
                <tr>
                    <td><code>{$name}</code></td>
                    <td><code>{$type}</code></td>
                    <td>{$nullable}</td>
                    <td>{$default}</td>
                    <td>{$autoInc}</td>
                    <td>{$comment}</td>
                </tr>
HTML;
        }

        $html .= <<<'HTML'
            </tbody>
        </table>

HTML;

        return $html;
    }

    /**
     * Render indexes table for a table.
     *
     * @param array<Index> $indexes
     *
     * @return string
     */
    private function renderIndexesTable(array $indexes): string
    {
        // Sort indexes alphabetically for deterministic output
        $sortedIndexes = $indexes;
        usort($sortedIndexes, fn(Index $a, Index $b) => strcmp($a->getName(), $b->getName()));

        $html = <<<'HTML'
        <h3>Indexes</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Columns</th>
                    <th>Unique</th>
                    <th>Primary</th>
                </tr>
            </thead>
            <tbody>
HTML;

        foreach ($sortedIndexes as $index) {
            $name = $this->escape($index->getName());
            $columns = implode(', ', array_map(fn($col) => $this->escape($col), $index->getColumnNames()));
            $unique = $index->isUnique() ? '✓' : '—';
            $primary = $index->isPrimary() ? '✓' : '—';

            $html .= <<<HTML
                <tr>
                    <td><code>{$name}</code></td>
                    <td><code>{$columns}</code></td>
                    <td>{$unique}</td>
                    <td>{$primary}</td>
                </tr>
HTML;
        }

        $html .= <<<'HTML'
            </tbody>
        </table>

HTML;

        return $html;
    }

    /**
     * Render foreign keys table for a table.
     *
     * @param array<ForeignKey> $foreignKeys
     *
     * @return string
     */
    private function renderForeignKeysTable(array $foreignKeys): string
    {
        // Sort foreign keys alphabetically for deterministic output
        $sortedFKs = $foreignKeys;
        usort($sortedFKs, fn(ForeignKey $a, ForeignKey $b) => strcmp($a->getName(), $b->getName()));

        $html = <<<'HTML'
        <h3>Foreign Keys</h3>
        <table>
            <thead>
                <tr>
                    <th>Constraint</th>
                    <th>Local Column(s)</th>
                    <th>References</th>
                    <th>ON DELETE</th>
                    <th>ON UPDATE</th>
                </tr>
            </thead>
            <tbody>
HTML;

        foreach ($sortedFKs as $fk) {
            $name = $this->escape($fk->getName());
            $localCols = implode(', ', array_map(fn($col) => $this->escape($col), $fk->getLocalColumns()));
            $refTable = $this->escape($fk->getReferencedTable());
            $refCols = implode(', ', array_map(fn($col) => $this->escape($col), $fk->getReferencedColumns()));
            $onDelete = $fk->getOnDelete() ? $this->escape($fk->getOnDelete()) : '—';
            $onUpdate = $fk->getOnUpdate() ? $this->escape($fk->getOnUpdate()) : '—';

            $html .= <<<HTML
                <tr>
                    <td><code>{$name}</code></td>
                    <td><code>{$localCols}</code></td>
                    <td><code>{$refTable}({$refCols})</code></td>
                    <td>{$onDelete}</td>
                    <td>{$onUpdate}</td>
                </tr>
HTML;
        }

        $html .= <<<'HTML'
            </tbody>
        </table>

HTML;

        return $html;
    }

    /**
     * Render empty state (no tables).
     *
     * @return string
     */
    private function renderEmptyState(): string
    {
        return <<<'HTML'
    <div class="schema-info">
        <p class="empty">No tables found in this schema.</p>
    </div>

HTML;
    }

    /**
     * Render document footer (closing body and html tags).
     *
     * @return string
     */
    private function renderDocumentFooter(): string
    {
        return <<<'HTML'
</body>
</html>
HTML;
    }

    /**
     * Escape a string for safe HTML output.
     *
     * Escapes quotes, ampersands, and angle brackets to prevent XSS.
     *
     * @param string $value
     *
     * @return string
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, self::ESCAPE_FLAGS, self::CHARSET);
    }
}
