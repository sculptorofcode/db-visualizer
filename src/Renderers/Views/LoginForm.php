<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Login form for database connection credentials.
 *
 * Renders HTML form to collect:
 * - Host
 * - Port
 * - Username
 * - Password
 * - Database name
 */
final class LoginForm
{
    /**
     * Render login form HTML.
     *
     * @param string|null $error Optional error message to display
     * @return string HTML login form
     */
    public function render(?string $error = null): string
    {
        $errorHtml = '';
        if ($error !== null) {
            $errorHtml = sprintf(
                '<div class="error"><strong>Connection Error:</strong> %s</div>',
                htmlspecialchars($error)
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Visualizer - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info {
            background-color: #f0f7ff;
            border: 1px solid #b3d9ff;
            color: #0066cc;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔍 DB Visualizer</h1>
        <p class="subtitle">Schema Visualization Tool</p>

        $errorHtml

        <div class="info">
            <strong>MySQL/MariaDB Connection</strong><br>
            Enter your database credentials to visualize schema.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="host">Host</label>
                <input type="text" id="host" name="host" value="localhost" placeholder="localhost">
            </div>

            <div class="form-group">
                <label for="port">Port</label>
                <input type="number" id="port" name="port" value="3306" placeholder="3306">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="root" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="(leave empty if none)">
            </div>

            <div class="form-group">
                <label for="database">Database</label>
                <input type="text" id="database" name="database" placeholder="my_database" required>
            </div>

            <button type="submit">Connect & Visualize</button>
        </form>
    </div>
</body>
</html>
HTML;
    }
}
