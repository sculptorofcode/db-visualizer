<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Raised when visualization is attempted without explicit enable flag.
 *
 * Failure mode: Security-first constraint violated; visualization is disabled by default.
 */
class VisualizationDisabledException extends DatabaseVisualizerException
{
}
