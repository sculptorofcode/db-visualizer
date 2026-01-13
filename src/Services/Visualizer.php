<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Services;

use Sculptor\DbVisualizer\Contracts\Services\Renderer;
use Sculptor\DbVisualizer\Contracts\Services\Visualizer as VisualizerContract;
use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Exceptions\VisualizationDisabledException;

/**
 * Visualization orchestrator with explicit enable/disable gating.
 *
 * Security design: Visualization is DISABLED by default.
 * Must call enable() explicitly before rendering.
 *
 * This prevents accidental exposure of schema visualization
 * in environments where visualization should not be available.
 *
 * No rendering logic is implemented here; all rendering is delegated
 * to pluggable Renderer implementations.
 */
final class Visualizer implements VisualizerContract
{
    /**
     * Visualization enabled state (false by default for security).
     */
    private bool $enabled;

    /**
     * Create a new visualizer.
     *
     * @param Schema $schema Schema data to visualize
     * @param bool $enabled Whether visualization is enabled (default: false for security)
     */
    public function __construct(
        private readonly Schema $schema,
        bool $enabled = false,
    ) {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * {@inheritDoc}
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function renderJSON(): string
    {
        $this->guardEnabled('JSON');
        // JSON renderer will be implemented separately
        // For now, this method exists to satisfy the contract
        throw new VisualizationDisabledException(
            'JSON renderer not yet implemented'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderHTML(): string
    {
        $this->guardEnabled('HTML');
        // HTML renderer will be implemented separately
        // For now, this method exists to satisfy the contract
        throw new VisualizationDisabledException(
            'HTML renderer not yet implemented'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderDOT(): string
    {
        $this->guardEnabled('DOT');
        // DOT renderer will be implemented separately
        // For now, this method exists to satisfy the contract
        throw new VisualizationDisabledException(
            'DOT renderer not yet implemented'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function render(Renderer $renderer): string
    {
        $this->guardEnabled($renderer->getName());
        return $renderer->render($this->schema);
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Guard clause: Verify visualization is enabled before rendering.
     *
     * @param string $format Format name (e.g., 'JSON', 'HTML') for error message
     *
     * @throws VisualizationDisabledException if not enabled
     */
    private function guardEnabled(string $format): void
    {
        if (!$this->enabled) {
            throw new VisualizationDisabledException(
                "Cannot render {$format}: visualization is disabled. Call enable() first."
            );
        }
    }
}
