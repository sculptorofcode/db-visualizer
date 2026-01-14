<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers;

use Sculptor\DbVisualizer\Contracts\Services\Renderer;
use Sculptor\DbVisualizer\Contracts\Values\Column;
use Sculptor\DbVisualizer\Contracts\Values\ForeignKey;
use Sculptor\DbVisualizer\Contracts\Values\Index;
use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Contracts\Values\Table;
use Sculptor\DbVisualizer\Renderers\Views\ColumnsTable;
use Sculptor\DbVisualizer\Renderers\Views\DocumentFooter;
use Sculptor\DbVisualizer\Renderers\Views\DocumentHead;
use Sculptor\DbVisualizer\Renderers\Views\EmptyState;
use Sculptor\DbVisualizer\Renderers\Views\ForeignKeysTable;
use Sculptor\DbVisualizer\Renderers\Views\IndexesTable;
use Sculptor\DbVisualizer\Renderers\Views\Navigation;
use Sculptor\DbVisualizer\Renderers\Views\TablePage;

/**
 * HTML Renderer for schema visualization.
 *
 * Converts a Schema value object to semantic HTML with modern, compact design.
 * Uses anchor-based pagination with :target CSS selector for table navigation.
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
 * Navigation:
 * - Each table rendered as a separate page section with anchor ID
 * - Only one table visible at a time via :target CSS selector
 * - URL fragments control which table is displayed (#table-name)
 * - First table visible by default
 *
 * Determinism:
 * - Tables sorted alphabetically by name
 * - Navigation list sorted alphabetically
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
     * Available databases for selector dropdown.
     *
     * @var array<string>
     */
    private array $availableDatabases = [];

    /**
     * Logout URL for display in UI.
     *
     * @var string|null
     */
    private ?string $logoutUrl = null;

    /**
     * Set the list of available databases for the selector dropdown.
     *
     * @param array<string> $databases List of database names
     *
     * @return self
     */
    public function setAvailableDatabases(array $databases): self
    {
        $this->availableDatabases = $databases;
        return $this;
    }

    /**
     * Set the logout URL for display in UI.
     *
     * @param string|null $url Logout URL (e.g., "?logout=1")
     *
     * @return self
     */
    public function setLogoutUrl(?string $url): self
    {
        $this->logoutUrl = $url;
        return $this;
    }

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
        $dbName = $this->escape($schema->getName());
        $engine = $this->escape($schema->getEngine());
        $tableCount = count($schema->getTables());
        
        $html = DocumentHead::render($dbName);

        $tables = $schema->getTables();
        if ($tables) {
            // Sort tables alphabetically for deterministic output
            usort($tables, fn(Table $a, Table $b) => strcmp($a->getName(), $b->getName()));

            // Build table navigation links
            $tableLinks = [];
            foreach ($tables as $table) {
                $tableLinks[$this->sanitizeAnchorId($table->getName())] = $this->escape($table->getName());
            }

            // Render navigation with database info and available databases
            $html .= Navigation::render($dbName, $this->availableDatabases, $engine, $tableCount, $tableLinks, $this->logoutUrl);

            // Render table pages
            foreach ($tables as $table) {
                $html .= $this->renderTablePage($table);
            }
        } else {
            // Render navigation even when no tables (shows database info)
            $html .= Navigation::render($dbName, $this->availableDatabases, $engine, $tableCount, [], $this->logoutUrl);
            $html .= EmptyState::render();
        }

        $html .= DocumentFooter::render();

        return $html;
    }

    /**
     * Render a table page (section with anchor ID).
     *
     * @param Table $table
     *
     * @return string
     */
    private function renderTablePage(Table $table): string
    {
        $anchorId = $this->sanitizeAnchorId($table->getName());
        $tableName = $this->escape($table->getName());
        $tableType = $this->escape($table->getType() ?? '');
        $comment = $table->getComment() ? $this->escape($table->getComment()) : null;

        $html = TablePage::renderHeader($anchorId, $tableName, $comment, $tableType ?: null);

        // Columns section
        $html .= ColumnsTable::render($table->getColumns(), $this->escape(...));

        // Indexes section
        if ($table->getIndexes()) {
            $html .= IndexesTable::render($table->getIndexes(), $this->escape(...));
        }

        // Foreign keys section
        if ($table->getForeignKeys()) {
            $html .= ForeignKeysTable::render($table->getForeignKeys(), $this->escape(...));
        }

        $html .= TablePage::renderFooter();

        return $html;
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

    /**
     * Sanitize a string for use as an HTML anchor ID.
     *
     * Converts table name to valid anchor ID:
     * - Lowercase
     * - Replace spaces and special chars with hyphens
     * - Remove leading/trailing hyphens
     *
     * @param string $name
     *
     * @return string
     */
    private function sanitizeAnchorId(string $name): string
    {
        $id = strtolower($name);
        $id = preg_replace('/[^a-z0-9]+/', '-', $id);
        $id = trim($id, '-');
        return $id ?: 'table';
    }
}
