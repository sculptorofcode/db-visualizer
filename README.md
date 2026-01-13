# DB Visualizer

**DB Visualizer** is a  **read-only, framework-agnostic database schema introspection and visualization library for PHP** .

It is designed to safely inspect database **structure only** (tables, columns, indexes, foreign keys) using  **Core PHP + PDO** , with zero data access and zero schema mutation.

---

## ✨ Features

* 🔍 **Schema-only introspection**
  * Tables
  * Columns
  * Indexes
  * Foreign keys
* 🔐 **Security-first design**
  * Read-only by design
  * No data queries
  * Visualization disabled by default
* 🧩 **Framework-agnostic**
  * Works with Core PHP
  * No Laravel / Symfony / CI dependency
* 🔌 **Adapter-based architecture**
  * MySQL adapter included
  * Easy to extend for PostgreSQL, SQLite
* 📦 **Composer-first**
  * PSR-4 autoloading
  * Clean dependency graph
* 🧪 **Deterministic output**
  * Stable JSON for testing & tooling

---

## 📦 Installation

```bash
composer require sculptor/db-visualizer
```

**Requirements**

* PHP **8.1+**
* PDO extension
* Supported DB: **MySQL / MariaDB** (initial release)

---

## 🚀 Quick Start (JSON Schema Preview)

```php
<?php
require 'vendor/autoload.php';

use Sculptor\DbVisualizer\Services\ConnectionHandler;
use Sculptor\DbVisualizer\Services\Visualizer;
use Sculptor\DbVisualizer\Renderers\JSONRenderer;

// Create PDO connection
$pdo = new PDO(
    'mysql:host=localhost;dbname=my_database',
    'username',
    'password',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);

// Initialize connection handler
$connection = new ConnectionHandler($pdo, 'my_database');

// Extract schema (metadata only)
$schema = $connection->getIntrospector()->schema();

// Create visualizer (disabled by default)
$visualizer = new Visualizer($schema);

// Explicitly enable visualization
$visualizer->enable();

// Render JSON
$renderer = new JSONRenderer();

header('Content-Type: application/json');
echo $visualizer->render($renderer);
```

---

## 🧱 Architecture Overview

```
PDO
 ↓
ConnectionHandler
 ↓
DriverAdapter (MySQL)
 ↓
Schema (Immutable Snapshot)
 ↓
Visualizer (Explicit Enable Gate)
 ↓
Renderer (JSON / HTML / DOT)
```

### Key Design Principles

* **Contracts first** (interfaces over implementations)
* **Adapters isolate database-specific logic**
* **Renderers never access the database**
* **Schema objects are immutable**
* **No side effects**

---

## 🔐 Security Model

DB Visualizer is intentionally restrictive:

* ❌ No row or data access
* ❌ No schema mutation
* ❌ No file I/O
* ❌ No auto-execution or auto-exposure

### Visualization Gate

Rendering is  **disabled by default** :

```php
$visualizer->render($renderer); // ❌ throws exception
$visualizer->enable();
$visualizer->render($renderer); // ✅ allowed
```

This prevents accidental exposure in production environments.

---

## 📤 Output Formats

### ✅ JSON (Available)

* Deterministic ordering
* Fully escaped identifiers
* Tooling & API friendly

### ⏳ Planned

* HTML (read-only, escaped)
* DOT / GraphViz (ER diagrams)

---

## 🧩 Extending the Library

### Custom Database Adapter

Implement the `DriverAdapter` and `SchemaIntrospector` contracts, then register:

```php
$resolver->register('pgsql', PostgresAdapter::class);
```

### Custom Renderer

Implement the `Renderer` contract:

```php
class MyRenderer implements Renderer
{
    public function getName(): string {}
    public function getMimeType(): string {}
    public function render(Schema $schema): string {}
}
```

---

## 🧪 Testing Philosophy

The library is designed to be testable  **without** :

* Frameworks
* Database servers (PDO can be mocked)
* Filesystem access
* Global state

JSON output is deterministic to support snapshot testing.

---

## 🗺 Roadmap

* [ ] HTML Renderer
* [ ] PostgreSQL Adapter
* [ ] SQLite Adapter
* [ ] DOT / ER Diagram Renderer
* [ ] PHPUnit test suite
* [ ] CLI (optional, last)

---

## 📄 License

MIT License © sculptorofcode

---

## 🤝 Contributing

Contributions are welcome, especially:

* New database adapters
* Renderers
* Tests
* Performance improvements

Please keep contributions:

* Framework-agnostic
* Read-only
* Security-first

---

## ⭐ Why This Library Exists

Most schema visualizers are:

* Framework-coupled
* UI-first
* Unsafe by default

**DB Visualizer** is built as a  **core infrastructure tool** :

* Minimal
* Auditable
* Extensible
* Safe
