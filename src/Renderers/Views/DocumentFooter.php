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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableLinks = document.querySelectorAll('.table-list a');
        const firstLink = tableLinks[0];
        if (firstLink) {
            firstLink.click();
        }
    });
</script>
</body>
</html>
HTML;
    }
}
