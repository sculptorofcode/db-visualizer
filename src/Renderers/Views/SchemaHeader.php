<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders the schema header with database name, engine, and table count.
 */
final class SchemaHeader
{
    /**
     * Render the schema header section.
     *
     * @param string $dbName Escaped database name
     * @param string $engine Escaped engine name
     * @param int $tableCount Number of tables
     *
     * @return string
     */
    public static function render(string $dbName, string $engine, int $tableCount): string
    {
        return <<<HTML
        <div class="sidebar-db-info">
            <div class="db-name"><strong>Database:</strong> {$dbName}</div>
            <div class="db-engine"><strong>Engine:</strong> {$engine}</div>
            <div class="db-count"><strong>Tables:</strong> {$tableCount}</div>
        </div>
HTML;
    }
}
