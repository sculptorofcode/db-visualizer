<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Raised when schema introspection fails for a specific schema element.
 *
 * Failure mode: Introspection query failed for table, column, index, or constraint.
 */
class SchemaAccessException extends DatabaseVisualizerException
{
}
