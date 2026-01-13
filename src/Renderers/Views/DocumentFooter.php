<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders the HTML document footer.
 */
final class DocumentFooter
{
    /**
     * Render the document footer.
     *
     * @return string
     */
    public static function render(): string
    {
        return <<<'HTML'
        </div>
    </div>
</div>
</body>
</html>
HTML;
    }
}
