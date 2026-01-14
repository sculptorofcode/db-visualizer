<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Services;

use PDO;
use PDOException;
use Sculptor\DbVisualizer\Renderers\Views\LoginForm;

/**
 * Dashboard orchestrator for login + schema visualization.
 *
 * Handles complete flow:
 * 1. Shows login form if credentials not provided
 * 2. Validates database connection
 * 3. Introspects and visualizes schema
 * 4. Handles database switching
 * 5. Stores credentials in session for persistence across database switches
 */
final class Dashboard
{
    private ?PDO $pdo = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $database = null;
    private ?string $error = null;

    private const SESSION_KEY = 'db_visualizer_credentials';

    /**
     * Create a new dashboard.
     */
    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Process credentials from POST request.
     *
     * @return bool True if credentials were processed and connection succeeded
     */
    public function processCredentials(): bool
    {
        // Check if credentials exist in session first
        if (isset($_SESSION[self::SESSION_KEY])) {
            $creds = $_SESSION[self::SESSION_KEY];
            $this->host = $creds['host'];
            $this->port = $creds['port'];
            $this->username = $creds['username'];
            $this->password = $creds['password'];
            $this->database = $creds['database'];
            
            return $this->establishConnection();
        }

        // Otherwise, process POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        try {
            $this->host = $_POST['host'] ?? 'localhost';
            $this->port = (int) ($_POST['port'] ?? 3306);
            $this->username = $_POST['username'] ?? '';
            $this->password = $_POST['password'] ?? '';
            $this->database = $_POST['database'] ?? '';

            if (!$this->username || !$this->database) {
                throw new \Exception('Username and database are required');
            }

            // Try to establish connection
            if ($this->establishConnection()) {
                // Store credentials in session for future requests
                $_SESSION[self::SESSION_KEY] = [
                    'host' => $this->host,
                    'port' => $this->port,
                    'username' => $this->username,
                    'password' => $this->password,
                    'database' => $this->database,
                ];
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Establish PDO connection with current credentials.
     *
     * @return bool True if connection succeeded
     */
    private function establishConnection(): bool
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};charset=utf8mb4";
            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
            return true;
        } catch (PDOException $e) {
            $this->error = "Database connection failed: {$e->getMessage()}";
            return false;
        }
    }

    /**
     * Clear stored session credentials (logout).
     */
    public function clearSession(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Check if credentials are available (from POST).
     *
     * @return bool True if PDO connection is established
     */
    public function hasConnection(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * Get the PDO connection.
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * Get the current database name.
     *
     * @return string|null
     */
    public function getDatabase(): ?string
    {
        return $this->database;
    }

    /**
     * Get connection error message.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Render login form.
     *
     * @return string HTML login form
     */
    public function renderLoginForm(): string
    {
        $form = new LoginForm();
        return $form->render($this->error);
    }

    /**
     * Complete render method - handles everything.
     *
     * Handles:
     * - Logout action (destroys session)
     * - Login form display
     * - Credential processing
     * - Schema visualization
     * - Database switching
     *
     * @return string HTML output (login form or schema visualization)
     */
    public function render(): string
    {
        // Handle logout
        if (isset($_GET['logout'])) {
            $this->clearSession();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        // Process credentials (POST or session)
        if (!$this->processCredentials()) {
            // Show login form if no connection
            return $this->renderLoginForm();
        }

        // Get connection and current database
        $pdo = $this->getConnection();
        $database = $this->getDatabase();

        // Handle database switching from query parameter
        $currentDatabase = $_GET['database'] ?? $database;

        // Create visualizer
        $connectionHandler = new ConnectionHandler($pdo, $currentDatabase);
        $schema = $connectionHandler->getIntrospector()->schema($currentDatabase);

        $visualizer = new Visualizer($schema, $connectionHandler, true);
        $visualizer->enable();

        // Render schema with logout button
        return $visualizer->render(
            $visualizer->getHTMLRenderer()
                ->setLogoutUrl('?logout=1')
        );
    }
}
