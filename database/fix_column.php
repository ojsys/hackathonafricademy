<?php
require_once __DIR__ . '/../config/database.php';

// Show which SQLite file is actually being used
echo '<pre>';
echo 'SQLite file: ' . SQLITE_PATH . "\n";
echo 'File exists: ' . (file_exists(SQLITE_PATH) ? 'YES' : 'NO') . "\n\n";

// Check current columns on qualifying_attempts
$cols = db()->query('PRAGMA table_info(qualifying_attempts)')->fetchAll(PDO::FETCH_ASSOC);
echo "Current columns in qualifying_attempts:\n";
foreach ($cols as $c) echo '  - ' . $c['name'] . "\n";
echo "\n";

// Apply the fix
try {
    db()->exec('ALTER TABLE qualifying_attempts ADD COLUMN question_ids_json TEXT');
    echo "✅ Column question_ids_json added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate') !== false) {
        echo "ℹ️ Column already exists — no action needed.\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Verify
$cols2 = db()->query('PRAGMA table_info(qualifying_attempts)')->fetchAll(PDO::FETCH_ASSOC);
echo "\nColumns after fix:\n";
foreach ($cols2 as $c) echo '  - ' . $c['name'] . "\n";
echo '</pre>';
echo '<p><strong>Delete this file from the server after running it.</strong></p>';
