<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Services;

use Sculptor\DbVisualizer\Contracts\Services\ConnectionHandler as ConnectionHandlerContract;
use Sculptor\DbVisualizer\Contracts\Services\SchemaIntrospector;
use Sculptor\DbVisualizer\Exceptions\InvalidConnectionException;
use Sculptor\DbVisualizer\Exceptions\UnsupportedDatabaseEngineException;
use PDO;
use PDOException;

/**
 * Handles database connection management and routing to driver adapters.
 *
 * Accepts a PDO instance, validates it, detects the database engine,
 * and returns the appropriate SchemaIntrospector (driver adapter).
 *
 * Failure modes:
 * - InvalidConnectionException: PDO is not usable
 * - UnsupportedDatabaseEngineException: Database engine not supported
 */
final class ConnectionHandler implements ConnectionHandlerContract
{
    /**
     * The resolved driver adapter for this connection.
     *
     * @var SchemaIntrospector|null
     */
    private ?SchemaIntrospector $introspector = null;

    /**
     * Adapter resolver (injected, allows customization of supported engines).
     */
    private readonly AdapterResolver $resolver;

    /**
     * Create a new connection handler.
     *
     * @param PDO $pdo PDO instance
     * @param string|null $database Database name to introspect
     *
     * @throws InvalidConnectionException if PDO is not valid
     * @throws UnsupportedDatabaseEngineException if database engine is not supported
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly ?string $database = null
    ) {
        $this->resolver = new AdapterResolver();
        $this->validateAndResolveAdapter();
    }

    /**
     * {@inheritDoc}
     */
    public function getEngine(): string
    {
        try {
            return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (PDOException $e) {
            throw new InvalidConnectionException(
                "Cannot determine PDO driver: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * {@inheritDoc}
     */
    public function getIntrospector(): SchemaIntrospector
    {
        if ($this->introspector === null) {
            $this->introspector = $this->resolver->resolve($this->pdo, $this->database);
        }

        return $this->introspector;
    }

    /**
     * {@inheritDoc}
     */
    public function getCapabilities(): array
    {
        return $this->getIntrospector()->getCapabilities($this->pdo);
    }

    /**
     * Validate the PDO connection and resolve the appropriate adapter.
     *
     * @throws InvalidConnectionException
     * @throws UnsupportedDatabaseEngineException
     */
    private function validateAndResolveAdapter(): void
    {
        try {
            // Verify PDO is usable by testing basic connectivity
            $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (PDOException $e) {
            throw new InvalidConnectionException(
                "PDO connection is not usable: {$e->getMessage()}",
                previous: $e
            );
        }

        // Attempt to resolve the adapter (may throw UnsupportedDatabaseEngineException)
        $this->resolver->resolve($this->pdo, $this->database);
    }
}
