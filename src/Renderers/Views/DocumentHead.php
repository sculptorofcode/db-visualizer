<?php

declare(strict_types=1);

namespace Sculptor\DbVisualizer\Renderers\Views;

/**
 * Renders the HTML document head with inline styles.
 */
final class DocumentHead
{
    /**
     * Render the document head section.
     *
     * @param string $dbName Escaped database name
     *
     * @return string
     */
    public static function render(string $dbName): string
    {
        $escapedName = htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schema: $escapedName</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c3e50;
            --bg-color: #ecf0f1;
            --border-color: #bdc3c7;
            --text-color: #2c3e50;
            --hover-bg: #d5dbdb;
            --white: #ffffff;
        }

        html, body {
            height: 100vh;
            overflow: hidden;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            display: flex;
            height: 100vh;
            gap: 0;
        }

        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 2px solid var(--primary-color);
            overflow-y: auto;
            padding: 0.75rem;
            flex-shrink: 0;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .sidebar-header h3 {
            font-size: 0.95em;
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .sidebar-db-info {
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0.6rem;
            margin-bottom: 0.75rem;
        }

        .db-name, .db-engine, .db-count {
            font-size: 0.85em;
            margin-bottom: 0.25rem;
            color: var(--text-color);
        }

        .db-name:last-child, .db-engine:last-child, .db-count:last-child {
            margin-bottom: 0;
        }

        .db-name strong, .db-engine strong, .db-count strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .db-selector-form {
            margin-bottom: 0.75rem;
        }

        .db-selector {
            width: 100%;
            padding: 0.4rem 0.5rem;
            font-size: 0.85em;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: var(--white);
            color: var(--text-color);
            cursor: pointer;
            font-family: inherit;
        }

        .db-selector:hover {
            border-color: var(--primary-color);
        }

        .db-selector:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.1);
        }

        .sidebar-section {
            margin-bottom: 0.75rem;
        }

        .sidebar-section p {
            font-size: 0.9em;
            margin-bottom: 0.5rem;
        }

        .sidebar-section h4 {
            font-size: 0.9em;
            color: var(--primary-color);
            margin-bottom: 0.4rem;
            font-weight: 600;
        }

        .table-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .table-list li {
            margin: 0;
        }

        .table-list a {
            display: block;
            padding: 0.3rem 0.6rem;
            color: var(--text-color);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            font-size: 0.9em;
            font-weight: 500;
        }

        .table-list a:hover {
            background: var(--hover-bg);
            border-left-color: var(--primary-color);
        }

        .table-list a:target {
            background: #e8f4f8;
            border-left-color: var(--primary-color);
            font-weight: 600;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-header {
            background: var(--white);
            border-bottom: 2px solid var(--primary-color);
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .content-header h1 {
            font-size: 1.8em;
            margin: 0;
            color: var(--primary-color);
        }

        .content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 1.5rem;
        }

        .table-page {
            display: none;
        }

        .table-page:target {
            display: block;
        }

        .section {
            margin-bottom: 1.25rem;
        }

        .section-title {
            font-size: 1em;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            font-size: 0.9em;
            margin-bottom: 0.75rem;
        }

        th {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.4rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85em;
        }

        td {
            padding: 0.4rem;
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:hover {
            background: var(--hover-bg);
        }

        code {
            background: #f5f5f5;
            padding: 0.2em 0.4em;
            border-radius: 3px;
            font-family: "Courier New", monospace;
            font-size: 0.85em;
            color: var(--primary-color);
        }

        .empty {
            text-align: center;
            padding: 1.5rem;
            color: #999;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 2px solid var(--primary-color);
                max-height: 200px;
            }

            .table-list a {
                display: inline-block;
                margin-right: 0.5rem;
                border-left: none;
                border-bottom: 3px solid transparent;
            }

            .table-list a:hover,
            .table-list a:target {
                border-bottom-color: var(--primary-color);
                border-left: none;
            }

            .content {
                padding: 1rem;
            }
        }

        .header-meta {
            display: flex;
            gap: 2rem;
            font-size: 0.95em;
            color: #555;
        }

        .header-meta strong {
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>DB Visualizer</h3>
        </div>
HTML;
    }
}
