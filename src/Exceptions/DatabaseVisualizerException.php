<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Base exception for all database visualizer errors.
 *
 * All exceptions in the package extend this contract.
 * Enables granular error handling for calling code.
 */
class DatabaseVisualizerException extends \Exception
{
}
