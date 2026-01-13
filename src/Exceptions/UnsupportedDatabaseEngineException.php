<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Raised when the database engine is not supported by the visualizer.
 *
 * Failure mode: Driver adapter for requested database engine does not exist.
 */
class UnsupportedDatabaseEngineException extends DatabaseVisualizerException
{
}
