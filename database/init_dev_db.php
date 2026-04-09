<?php

require_once __DIR__ . '/../config/database.php';

echo "Initializing development SQLite database...\n";

$sqlitePath = SQLITE_PATH;
$schemaSqlPath = __DIR__ . '/schema_sqlite.sql';
$seedSqlPath = __DIR__ . '/seed.sql';

// 1. Remove existing SQLite database file if it exists
if (file_exists($sqlitePath)) {
    echo "Removing existing database file: $sqlitePath\n";
    unlink($sqlitePath);
}

try {
    // Connect to (or create) the SQLite database
    $pdo = new PDO('sqlite:' . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Execute the schema_sqlite.sql to create the database schema
    echo "Executing schema: $schemaSqlPath\n";
    $schemaSql = file_get_contents($schemaSqlPath);
    if ($schemaSql === false) {
        throw new Exception("Could not read schema file: $schemaSqlPath");
    }
    $pdo->exec($schemaSql);
    echo "Schema created successfully.\n";

    // 3. Execute the seed.sql to seed the database with initial data
    echo "Executing seed data: $seedSqlPath\n";
    $seedSql = file_get_contents($seedSqlPath);
    if ($seedSql === false) {
        throw new Exception("Could not read seed file: $seedSqlPath");
    }
    $pdo->exec($seedSql);
    echo "Database seeded successfully.\n";

    echo "Development SQLite database initialized completely.\n";

} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    die("Database initialization failed: " . $e->getMessage());
}

