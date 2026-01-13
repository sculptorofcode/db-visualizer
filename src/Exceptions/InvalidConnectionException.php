<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Raised when the provided PDO connection is invalid or not connected.
 *
 * Failure mode: PDO instance is not usable, or connection has failed.
 */
class InvalidConnectionException extends DatabaseVisualizerException
{
}
