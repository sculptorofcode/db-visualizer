<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

use Sculptor\DbVisualizer\Contracts\Values\ForeignKey;

/**
 * Renders the foreign keys table section.
 */
final class ForeignKeysTable
{
    /**
     * Render the foreign keys table.
     *
     * @param array<ForeignKey> $foreignKeys
     * @param callable(string): string $escape Escaping function
     *
     * @return string
     */
    public static function render(array $foreignKeys, callable $escape): string
    {
        // Sort foreign keys alphabetically for deterministic output
        $sortedFKs = $foreignKeys;
        usort($sortedFKs, fn(ForeignKey $a, ForeignKey $b) => strcmp($a->getName(), $b->getName()));

        $html = <<<'HTML'
    <div class="section">
        <div class="section-title">Foreign Keys</div>
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
            $name = $escape($fk->getName());
            $localCols = implode(', ', array_map($escape, $fk->getLocalColumns()));
            $refTable = $escape($fk->getReferencedTable());
            $refCols = implode(', ', array_map($escape, $fk->getReferencedColumns()));
            $onDelete = $fk->getOnDelete() ? $escape($fk->getOnDelete()) : '—';
            $onUpdate = $fk->getOnUpdate() ? $escape($fk->getOnUpdate()) : '—';

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
    </div>

HTML;

        return $html;
    }
}
