<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Drivers\MySQL;

use Sculptor\DbVisualizer\Contracts\Services\DriverAdapter;
use Sculptor\DbVisualizer\Contracts\Values\Schema;
use Sculptor\DbVisualizer\Contracts\Values\Table;
use Sculptor\DbVisualizer\Exceptions\InvalidConnectionException;
use Sculptor\DbVisualizer\Exceptions\PermissionDeniedException;
use Sculptor\DbVisualizer\Exceptions\SchemaAccessException;
use Sculptor\DbVisualizer\Values\Implementations\ColumnImplementation;
use Sculptor\DbVisualizer\Values\Implementations\ForeignKeyImplementation;
use Sculptor\DbVisualizer\Values\Implementations\IndexImplementation;
use Sculptor\DbVisualizer\Values\Implementations\SchemaImplementation;
use Sculptor\DbVisualizer\Values\Implementations\TableImplementation;
use PDO;
use PDOException;

/**
 * MySQL driver adapter.
 *
 * Implements schema introspection for MySQL/MariaDB via INFORMATION_SCHEMA and DESCRIBE.
 * All queries are metadata-only; no data access.
 *
 * Supports:
 * - Table discovery and metadata
 * - Column metadata (type, nullable, default, auto-increment)
 * - Index metadata (unique, primary, column composition)
 * - Foreign key metadata (constraints, actions)
 *
 * Uses prepared statements for all queries; treats schema names as untrusted input.
 */
final class MySQLAdapter implements DriverAdapter
{
    /**
     * Cached schema snapshot.
     *
     * @var Schema|null
     */
    private ?Schema $cachedSchema = null;

    /**
     * @param PDO $pdo PDO instance configured for MySQL
     * @param string $database Database name to introspect
     *
     * @throws InvalidConnectionException if PDO is not usable
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $database,
    ) {
        $this->validateConnection();
    }

    /**
     * {@inheritDoc}
     */
    public function engineName(): string
    {
        return 'mysql';
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(PDO $pdo): bool
    {
        try {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            return $driver === 'mysql';
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCapabilities(PDO $pdo): array
    {
        try {
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            return [
                'engine' => 'mysql',
                'version' => $version,
                'supports_foreign_keys' => true,
                'supports_views' => true,
                'max_table_name_length' => 64,
                'max_column_name_length' => 64,
            ];
        } catch (PDOException) {
            return [
                'engine' => 'mysql',
                'version' => 'unknown',
                'supports_foreign_keys' => true,
                'supports_views' => true,
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function schema(): Schema
    {
        if ($this->cachedSchema !== null) {
            return $this->cachedSchema;
        }

        try {
            $tables = [];
            $tableNames = $this->tableNames();

            foreach ($tableNames as $tableName) {
                $tables[] = $this->table($tableName);
            }

            $this->cachedSchema = new SchemaImplementation(
                $this->database,
                'mysql',
                array_filter($tables, fn($t) => $t !== null),
            );

            return $this->cachedSchema;
        } catch (PDOException $e) {
            throw new SchemaAccessException(
                "Failed to introspect schema: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function table(string $name): ?Table
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE 
                 FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
            );
            $stmt->execute([$this->database, $name]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            $columns = $this->extractColumnsForTable($name);
            $indexes = $this->extractIndexesForTable($name);
            $foreignKeys = $this->extractForeignKeysForTable($name);

            return new TableImplementation(
                name: $name,
                schema: $this->database,
                columns: $columns,
                indexes: $indexes,
                foreignKeys: $foreignKeys,
                comment: $row['TABLE_COMMENT'] ?: null,
                type: $row['TABLE_TYPE'],
            );
        } catch (PDOException $e) {
            throw new SchemaAccessException(
                "Failed to introspect table '{$name}': {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tableNames(): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                 WHERE TABLE_SCHEMA = ? 
                 ORDER BY TABLE_NAME'
            );
            $stmt->execute([$this->database]);
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $results ?: [];
        } catch (PDOException $e) {
            if ($this->isPermissionError($e)) {
                throw new PermissionDeniedException(
                    "Cannot access INFORMATION_SCHEMA.TABLES for database '{$this->database}'",
                    previous: $e
                );
            }

            throw new SchemaAccessException(
                "Failed to retrieve table list: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tablesWithForeignKeys(): array
    {
        $schema = $this->schema();
        return $schema->getTablesWithForeignKeys();
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return count($this->tableNames()) === 0;
    }

    /**
     * Extract all columns for a given table.
     *
     * @return array<ColumnImplementation>
     *
     * @throws SchemaAccessException
     */
    private function extractColumnsForTable(string $tableName): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT 
                    COLUMN_NAME,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_DEFAULT,
                    EXTRA,
                    COLUMN_COMMENT,
                    CHARACTER_MAXIMUM_LENGTH
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION'
            );
            $stmt->execute([$this->database, $tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = [];
            foreach ($rows as $row) {
                $columns[] = new ColumnImplementation(
                    name: $row['COLUMN_NAME'],
                    type: $row['COLUMN_TYPE'],
                    nullable: $row['IS_NULLABLE'] === 'YES',
                    default: $row['COLUMN_DEFAULT'],
                    autoIncrement: strpos($row['EXTRA'], 'auto_increment') !== false,
                    comment: $row['COLUMN_COMMENT'] ?: null,
                    maxLength: $row['CHARACTER_MAXIMUM_LENGTH'] ? (int) $row['CHARACTER_MAXIMUM_LENGTH'] : null,
                );
            }

            return $columns;
        } catch (PDOException $e) {
            throw new SchemaAccessException(
                "Failed to extract columns for table '{$tableName}': {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Extract all indexes for a given table.
     *
     * @return array<IndexImplementation>
     *
     * @throws SchemaAccessException
     */
    private function extractIndexesForTable(string $tableName): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT 
                    INDEX_NAME,
                    COLUMN_NAME,
                    NON_UNIQUE,
                    SEQ_IN_INDEX
                 FROM INFORMATION_SCHEMA.STATISTICS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY INDEX_NAME, SEQ_IN_INDEX'
            );
            $stmt->execute([$this->database, $tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $indexMap = [];
            foreach ($rows as $row) {
                $indexName = $row['INDEX_NAME'];
                if (!isset($indexMap[$indexName])) {
                    $indexMap[$indexName] = [
                        'name' => $indexName,
                        'columns' => [],
                        'non_unique' => (int) $row['NON_UNIQUE'],
                    ];
                }
                $indexMap[$indexName]['columns'][] = $row['COLUMN_NAME'];
            }

            $indexes = [];
            foreach ($indexMap as $indexData) {
                $indexes[] = new IndexImplementation(
                    name: $indexData['name'],
                    columnNames: $indexData['columns'],
                    unique: $indexData['non_unique'] === 0,
                    primary: $indexData['name'] === 'PRIMARY',
                );
            }

            return $indexes;
        } catch (PDOException $e) {
            throw new SchemaAccessException(
                "Failed to extract indexes for table '{$tableName}': {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Extract all foreign keys for a given table.
     *
     * @return array<ForeignKeyImplementation>
     *
     * @throws SchemaAccessException
     */
    private function extractForeignKeysForTable(string $tableName): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT
                    kcu.CONSTRAINT_NAME,
                    kcu.COLUMN_NAME,
                    kcu.REFERENCED_TABLE_NAME,
                    kcu.REFERENCED_COLUMN_NAME,
                    rc.UPDATE_RULE,
                    rc.DELETE_RULE
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                    ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                    AND rc.CONSTRAINT_NAME   = kcu.CONSTRAINT_NAME
                WHERE kcu.TABLE_SCHEMA = ?
                    AND kcu.TABLE_NAME   = ?
                    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION'
            );

            $stmt->execute([$this->database, $tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $foreignKeyMap = [];

            foreach ($rows as $row) {
                $constraintName = $row['CONSTRAINT_NAME'];

                if (!isset($foreignKeyMap[$constraintName])) {
                    $foreignKeyMap[$constraintName] = [
                        'name' => $constraintName,
                        'local_columns' => [],
                        'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                        'referenced_columns' => [],
                        // Defensive defaults for portability
                        'on_update' => $row['UPDATE_RULE'] ?? 'RESTRICT',
                        'on_delete' => $row['DELETE_RULE'] ?? 'RESTRICT',
                    ];
                }

                $foreignKeyMap[$constraintName]['local_columns'][] = $row['COLUMN_NAME'];
                $foreignKeyMap[$constraintName]['referenced_columns'][] = $row['REFERENCED_COLUMN_NAME'];
            }

            $foreignKeys = [];

            foreach ($foreignKeyMap as $fkData) {
                $foreignKeys[] = new ForeignKeyImplementation(
                    name: $fkData['name'],
                    localColumns: $fkData['local_columns'],
                    referencedTable: $fkData['referenced_table'],
                    referencedColumns: $fkData['referenced_columns'],
                    onDelete: $fkData['on_delete'],
                    onUpdate: $fkData['on_update'],
                );
            }

            return $foreignKeys;
        } catch (PDOException $e) {
            throw new SchemaAccessException(
                "Failed to extract foreign keys for table '{$tableName}': {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Verify the PDO connection is valid and points to MySQL.
     *
     * @throws InvalidConnectionException
     */
    private function validateConnection(): void
    {
        try {
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver !== 'mysql') {
                throw new InvalidConnectionException(
                    "PDO driver is '{$driver}', expected 'mysql'"
                );
            }

            // Verify basic connectivity by checking if INFORMATION_SCHEMA is accessible
            $this->pdo->query('SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA LIMIT 1');
        } catch (PDOException $e) {
            throw new InvalidConnectionException(
                "MySQL connection validation failed: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Check if a PDOException represents a permission error.
     *
     * MySQL permission errors typically have SQLSTATE '42000' or '42S02'.
     */
    private function isPermissionError(PDOException $e): bool
    {
        $sqlState = $e->getCode();
        return in_array((string) $sqlState, ['42000', 'HY000'], true);
    }
}
