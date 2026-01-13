<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

use Sculptor\DbVisualizer\Contracts\Values\Index;

/**
 * Renders the indexes table section.
 */
final class IndexesTable
{
    /**
     * Render the indexes table.
     *
     * @param array<Index> $indexes
     * @param callable(string): string $escape Escaping function
     *
     * @return string
     */
    public static function render(array $indexes, callable $escape): string
    {
        // Sort indexes alphabetically for deterministic output
        $sortedIndexes = $indexes;
        usort($sortedIndexes, fn(Index $a, Index $b) => strcmp($a->getName(), $b->getName()));

        $html = <<<'HTML'
    <div class="section">
        <div class="section-title">Indexes</div>
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
            $name = $escape($index->getName());
            $columns = implode(', ', array_map($escape, $index->getColumnNames()));
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
    </div>

HTML;

        return $html;
    }
}