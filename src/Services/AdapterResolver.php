<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Services;

use Sculptor\DbVisualizer\Contracts\Services\DriverAdapter;
use Sculptor\DbVisualizer\Drivers\MySQL\MySQLAdapter;
use Sculptor\DbVisualizer\Exceptions\UnsupportedDatabaseEngineException;
use PDO;

/**
 * Resolves the correct DriverAdapter for a given PDO connection.
 *
 * Currently supports:
 * - MySQL (via 'mysql' PDO driver)
 *
 * Future adapters can be registered via constructor dependency injection.
 */
final class AdapterResolver
{
    /**
     * Map of PDO driver name -> adapter class name.
     *
     * @var array<string, class-string<DriverAdapter>>
     */
    private readonly array $adapterMap;

    /**
     * @param array<string, class-string<DriverAdapter>>|null $customAdapters
     *   Optional map of custom adapters to register.
     *   Keys are PDO driver names (e.g., 'mysql', 'pgsql'), values are adapter class names.
     */
    public function __construct(?array $customAdapters = null)
    {
        // Default adapters
        $this->adapterMap = [
            'mysql' => MySQLAdapter::class,
        ];

        // Merge custom adapters if provided (allows extensibility without modifying this class)
        if ($customAdapters !== null) {
            foreach ($customAdapters as $driver => $adapterClass) {
                $this->adapterMap[$driver] = $adapterClass;
            }
        }
    }

    /**
     * Resolve and instantiate the appropriate DriverAdapter for the given PDO connection.
     *
     * @param PDO $pdo PDO instance
     * @param string $database Database name to introspect
     *
     * @return DriverAdapter
     *
     * @throws UnsupportedDatabaseEngineException if PDO driver is not registered
     */
    public function resolve(PDO $pdo, string $database): DriverAdapter
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if (!isset($this->adapterMap[$driver])) {
            throw new UnsupportedDatabaseEngineException(
                "No driver adapter registered for PDO driver '{$driver}'. Supported: " . 
                implode(', ', array_keys($this->adapterMap))
            );
        }

        $adapterClass = $this->adapterMap[$driver];

        return new $adapterClass($pdo, $database);
    }

    /**
     * Register a custom adapter at runtime.
     *
     * Allows extending adapter support without subclassing.
     *
     * @param string $driver PDO driver name (e.g., 'pgsql')
     * @param class-string<DriverAdapter> $adapterClass Adapter class name
     */
    public function register(string $driver, string $adapterClass): void
    {
        $this->adapterMap[$driver] = $adapterClass;
    }

    /**
     * Get the list of supported PDO drivers.
     *
     * @return array<string>
     */
    public function getSupportedDrivers(): array
    {
        return array_keys($this->adapterMap);
    }
}
