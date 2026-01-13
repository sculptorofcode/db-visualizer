<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders a table page section with header and content.
 */
final class TablePage
{
    /**
     * Render a table page header.
     *
     * @param string $anchorId Sanitized anchor ID
     * @param string $tableName Escaped table name
     * @param string|null $comment Escaped table comment
     * @param string|null $tableType Escaped table type
     *
     * @return string
     */
    public static function renderHeader(
        string $anchorId,
        string $tableName,
        ?string $comment,
        ?string $tableType
    ): string {
        $html = <<<HTML
<section id="{$anchorId}" class="table-page">
    <h2 style="color: var(--primary-color); margin-bottom: 0.5rem; margin-top: 0;">{$tableName}</h2>
HTML;

        if ($comment) {
            $html .= <<<HTML
    <p style="color: #666; font-style: italic; margin-bottom: 0.5rem; margin-top: 0;">{$comment}</p>
HTML;
        }

        if ($tableType) {
            $html .= <<<HTML
    <p style="color: #666; margin-bottom: 1rem; margin-top: 0.25rem;"><strong>Type:</strong> {$tableType}</p>
HTML;
        }

        return $html;
    }

    /**
     * Render a table page footer.
     *
     * @return string
     */
    public static function renderFooter(): string
    {
        return <<<'HTML'
</section>

HTML;
    }
}
