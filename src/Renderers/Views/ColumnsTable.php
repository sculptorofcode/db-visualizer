<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

use Sculptor\DbVisualizer\Contracts\Values\Column;

/**
 * Renders the columns table section.
 */
final class ColumnsTable
{
    /**
     * Render the columns table.
     *
     * @param array<Column> $columns
     * @param callable(string): string $escape Escaping function
     *
     * @return string
     */
    public static function render(array $columns, callable $escape): string
    {
        $html = <<<'HTML'
    <div class="section">
        <div class="section-title">Columns</div>
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
            $name = $escape($column->getName());
            $type = $escape($column->getType());
            $nullable = $column->isNullable() ? 'YES' : 'NO';
            $default = $column->getDefault() ? $escape($column->getDefault()) : '—';
            $autoInc = $column->isAutoIncrement() ? '✓' : '—';
            $comment = $column->getComment() ? $escape($column->getComment()) : '—';

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
    </div>

HTML;

        return $html;
    }
}
