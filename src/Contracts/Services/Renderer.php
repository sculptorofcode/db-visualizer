<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Services;

use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Exceptions\VisualizationDisabledException;

/**
 * Contract for rendering schema data to various output formats.
 *
 * Accepts a Schema object and renders it to a specific format.
 * All output escapes schema identifiers for the target format.
 *
 * Failure modes:
 * - VisualizationDisabledException: Rendering is explicitly disabled
 */
interface Renderer
{
    /**
     * Render the schema to the target format.
     *
     * @throws VisualizationDisabledException if rendering is disabled
     */
    public function render(Schema $schema): string;

    /**
     * Get the name of this renderer (e.g., 'json', 'html', 'dot').
     */
    public function getName(): string;

    /**
     * Get the MIME type for output (e.g., 'application/json', 'text/html').
     */
    public function getMimeType(): string;
}
