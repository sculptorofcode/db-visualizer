<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders the sidebar with database selector and table list.
 */
final class Navigation
{
    /**
     * Render the navigation section with database selector and table list.
     *
     * @param string $currentDbName Escaped current database name
     * @param array<string> $availableDatabases List of available database names
     * @param string $engine Escaped engine name
     * @param int $tableCount Number of tables
     * @param array<string, string> $tableLinks Map of anchor ID => escaped table name
     * @param string|null $logoutUrl Optional logout URL
     *
     * @return string
     */
    public static function render(
        string $currentDbName,
        array $availableDatabases,
        string $engine,
        int $tableCount,
        array $tableLinks,
        ?string $logoutUrl = null
    ): string {
        $dbInfoHtml = SchemaHeader::render($currentDbName, $engine, $tableCount);

        // Build database selector dropdown if multiple databases available
        $selectorHtml = '';
        if (count($availableDatabases) > 1) {
            $selectorHtml = '<form method="get" class="db-selector-form" style="margin-bottom: 0.75rem;">';
            $selectorHtml .= '<select name="database" class="db-selector" onchange="if(this.value) window.location.href=\'?database=\'+encodeURIComponent(this.value)">';
            $selectorHtml .= '<option value="">Select Database...</option>';
            
            foreach ($availableDatabases as $dbName) {
                $escapedDbName = htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8');
                $selected = $dbName === $currentDbName ? ' selected' : '';
                $selectorHtml .= '<option value="' . $escapedDbName . '"' . $selected . '>' . $escapedDbName . '</option>';
            }
            
            $selectorHtml .= '</select>';
            $selectorHtml .= '</form>';
        }

        // Add logout button if logout URL provided
        $logoutHtml = '';
        if ($logoutUrl !== null) {
            $logoutHtml = '<a href="' . htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') . '" class="logout-btn" style="display: block; text-align: center; padding: 0.6rem; background-color: #e74c3c; color: white; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600; margin-top: 1rem;">Logout</a>';
        }

        $html = $selectorHtml . $dbInfoHtml . $logoutHtml;

        $html .= <<<'HTML'
        <div class="sidebar-section">
            <h4>Tables:</h4>
            <ul class="table-list">
HTML;

        foreach ($tableLinks as $anchorId => $tableName) {
            $html .= <<<HTML
            <li><a href="#{$anchorId}">{$tableName}</a></li>
HTML;
        }

        // Close the table list, add main content wrapper
        return $html . <<<'HTML'
            </ul>
        </div>
    </aside>
    <div class="main-content">
        <div class="content-header">
            <h1>Schema Tables</h1>
        </div>
        <div class="content">
HTML;
    }
}
