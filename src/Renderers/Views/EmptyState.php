<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders the empty state when no tables are found.
 */
final class EmptyState
{
    /**
     * Render the empty state section.
     *
     * @return string
     */
    public static function render(): string
    {
        return <<<'HTML'
    <div class="empty">
        <p>No tables found in this schema.</p>
    </div>

HTML;
    }
}
