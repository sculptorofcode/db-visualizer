<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Exceptions;

/**
 * Raised when database permissions are insufficient for introspection.
 *
 * Failure mode: User lacks required permissions for INFORMATION_SCHEMA or metadata queries.
 */
class PermissionDeniedException extends DatabaseVisualizerException
{
}
