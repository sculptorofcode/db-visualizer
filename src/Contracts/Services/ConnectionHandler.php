<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Contracts\Services;

use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Exceptions\InvalidConnectionException;
use Sculptor\DbVisualizer\Exceptions\UnsupportedDatabaseEngineException;

/**
 * Contract for database connection management.
 *
 * Accepts a PDO instance and routes it to the appropriate driver adapter.
 * Verifies the connection and engine are supported before returning an introspector.
 *
 * Failure modes:
 * - InvalidConnectionException: PDO is not usable
 * - UnsupportedDatabaseEngineException: Driver adapter does not exist
 */
interface ConnectionHandler
{
    /**
     * Create a new connection handler.
     *
     * @param \PDO $pdo PDO instance (required for all database operations)
     * @param string|null $database Optional database/schema name to introspect
     *
     * @throws InvalidConnectionException if PDO is not valid
     * @throws UnsupportedDatabaseEngineException if database engine is not supported
     */
    public function __construct(\PDO $pdo, ?string $database = null);

    /**
     * Get the name of the database engine (e.g., 'mysql', 'pgsql', 'sqlite').
     */
    public function getEngine(): string;

    /**
     * Get the database or schema name this handler is connected to.
     */
    public function getDatabase(): string;

    /**
     * Get the schema introspector for this connection.
     *
     * Returns the same instance on repeated calls (introspector is bound to connection).
     */
    public function getIntrospector(): SchemaIntrospector;

    /**
     * Get driver-specific capabilities (e.g., supported features, version info).
     *
     * @return array<string, mixed>
     */
    public function getCapabilities(): array;

    /**
     * Get list of available databases on this connection.
     *
     * @return array<string> Sorted list of database names
     */
    public function getAvailableDatabases(): array;
}
