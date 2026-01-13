<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Services;

use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Exceptions\VisualizationDisabledException;

/**
 * Contract for the visualization orchestrator.
 *
 * Manages enabling/disabling visualization and coordinates rendering.
 * Visualization is DISABLED by default; must be explicitly enabled.
 *
 * Security design:
 * - Fails if rendering is attempted without explicit enable()
 * - Tracks enabled state explicitly
 * - No framework dependencies, no implicit globals
 *
 * Failure modes:
 * - VisualizationDisabledException: Render called without enable()
 */
interface Visualizer
{
    /**
     * Create a new visualizer.
     *
     * @param Schema $schema Schema data to visualize
     * @param ConnectionHandler $connectionHandler Handler for database connection operations
     * @param bool $enabled Whether visualization is enabled (default: false for security)
     */
    public function __construct(Schema $schema, ConnectionHandler $connectionHandler, bool $enabled = false);

    /**
     * Explicitly enable visualization.
     *
     * Mutation: This changes the internal enabled state.
     * Required before rendering.
     */
    public function enable(): void;

    /**
     * Explicitly disable visualization.
     */
    public function disable(): void;

    /**
     * Check whether visualization is currently enabled.
     */
    public function isEnabled(): bool;

    /**
     * Render the schema to JSON format.
     *
     * @throws VisualizationDisabledException if not enabled
     */
    public function renderJSON(): string;

    /**
     * Render the schema to HTML format.
     *
     * @throws VisualizationDisabledException if not enabled
     */
    public function renderHTML(): string;

    /**
     * Render the schema to GraphViz DOT format.
     *
     * @throws VisualizationDisabledException if not enabled
     */
    public function renderDOT(): string;

    /**
     * Render using a custom renderer.
     *
     * @throws VisualizationDisabledException if not enabled
     */
    public function render(Renderer $renderer): string;

    /**
     * Get the underlying schema being visualized.
     */
    public function getSchema(): Schema;

    /**
     * Get list of available databases on the connection.
     *
     * Metadata-only; requires connection to be initialized.
     * Returns list of accessible database names.
     *
     * @return array<string> List of database names, sorted alphabetically
     */
    public function getAvailableDatabases(): array;

    /**
     * Get a pre-configured HTML renderer with available databases.
     *
     * Convenience method that creates an HTMLRenderer and automatically
     * configures it with the list of available databases.
     *
     * @return Renderer HTML renderer ready to render the schema
     */
    public function getHTMLRenderer();  // Returns HTMLRenderer implementing Renderer
}