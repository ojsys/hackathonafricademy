<?php
/**
 * HackathonAfrica — Migration Runner
 *
 * Run from CLI:     php database/run_migrations.php
 * Run from browser: https://yourdomain.com/database/run_migrations.php?secret=YOUR_SECRET
 *
 * Set RUN_SECRET below to a strong password before uploading.
 */

define('RUN_SECRET', 'hackafricamigrate2024'); // ← change this before deploying

// ── Auth ─────────────────────────────────────────────────────────────────────
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    if (($_GET['secret'] ?? '') !== RUN_SECRET) {
        http_response_code(403);
        die('Forbidden. Append ?secret=YOUR_SECRET to the URL.');
    }
    header('Content-Type: text/html; charset=utf-8');
}

require_once __DIR__ . '/../config/database.php';

$db     = db();
$driver = DB_DRIVER; // 'sqlite' or 'mysql'

// ── Output helpers ────────────────────────────────────────────────────────────
function out(string $msg, string $type = 'info'): void {
    global $isCli;
    $colors = ['ok' => '✅', 'skip' => '⏭️', 'warn' => '⚠️', 'error' => '❌', 'info' => 'ℹ️'];
    $icon = $colors[$type] ?? 'ℹ️';
    if ($isCli) {
        echo $icon . ' ' . strip_tags($msg) . PHP_EOL;
    } else {
        $cls = ['ok' => 'success', 'skip' => 'secondary', 'warn' => 'warning', 'error' => 'danger', 'info' => 'info'];
        echo '<div class="alert alert-' . ($cls[$type] ?? 'info') . ' py-2 mb-2">' . $icon . ' ' . $msg . '</div>';
    }
}

// ── Ensure migrations table exists ───────────────────────────────────────────
if ($driver === 'mysql') {
    $db->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
} else {
    $db->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filename TEXT NOT NULL UNIQUE,
        ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
}

// ── Load already-run migrations ───────────────────────────────────────────────
$ran = [];
foreach ($db->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN) as $f) {
    $ran[$f] = true;
}

// ── Discover migration files (alphabetical = chronological by name) ───────────
$dir   = __DIR__ . '/migrations';
$files = glob($dir . '/*.sql');
sort($files);

// ── SQL adapter (same logic as setup_enhanced.php) ───────────────────────────
function adapt_sql(string $sql): string {
    global $driver;
    if ($driver !== 'mysql') return $sql;

    $sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $sql);
    $sql = preg_replace("/CHECK\s*\([^)]+\)/i", '', $sql);
    $sql = preg_replace('/\bINTEGER\s+DEFAULT/i',   'INT DEFAULT', $sql);
    $sql = preg_replace('/\bINTEGER\s+NOT\s+NULL/i','INT NOT NULL', $sql);
    return $sql;
}

// ── HTML shell (browser only) ─────────────────────────────────────────────────
if (!$isCli): ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DB Migrations — HackathonAfrica</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:720px">
<h2 class="mb-1">HackathonAfrica — Migration Runner</h2>
<p class="text-muted mb-4">Driver: <strong><?= h($driver) ?></strong></p>
<?php endif;

// ── Run migrations ────────────────────────────────────────────────────────────
$ran_count  = 0;
$skip_count = 0;
$err_count  = 0;

foreach ($files as $path) {
    $filename = basename($path);

    // Skip files marked as SQLite-only when running on MySQL
    $content = file_get_contents($path);
    $isSqliteOnly = stripos($content, '-- DRIVER: sqlite') !== false;
    if ($driver === 'mysql' && $isSqliteOnly) {
        out("<strong>$filename</strong> — skipped (SQLite-only migration)", 'skip');
        $skip_count++;
        continue;
    }

    // Skip files marked as MySQL-only comment files (no actual SQL statements)
    if ($driver === 'sqlite' && stripos($content, '-- DRIVER: mysql') !== false) {
        out("<strong>$filename</strong> — skipped (MySQL-only migration)", 'skip');
        $skip_count++;
        continue;
    }

    // Already ran?
    if (isset($ran[$filename])) {
        out("<strong>$filename</strong> — already applied", 'skip');
        $skip_count++;
        continue;
    }

    // Adapt and execute
    $sql = adapt_sql($content);

    // Split on semicolons to handle multi-statement files
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => $s !== '' && !preg_match('/^--/', $s)
    );

    $ok = true;
    foreach ($statements as $stmt) {
        // Skip pure-comment blocks
        if (preg_match('/^[\s\-\/\*]+$/', $stmt)) continue;
        try {
            $db->exec($stmt);
        } catch (PDOException $e) {
            // "Duplicate column" is safe to ignore (idempotent ALTER TABLE)
            $msg = $e->getMessage();
            if (stripos($msg, 'duplicate column') !== false || stripos($msg, 'already exists') !== false) {
                out("<strong>$filename</strong> — column already exists, skipping", 'warn');
            } else {
                out("<strong>$filename</strong> — ERROR: " . htmlspecialchars($msg), 'error');
                $ok = false;
                $err_count++;
                break;
            }
        }
    }

    if ($ok) {
        $insertSql = $driver === 'mysql'
            ? 'INSERT IGNORE INTO migrations (filename) VALUES (?)'
            : 'INSERT OR IGNORE INTO migrations (filename) VALUES (?)';
        $db->prepare($insertSql)->execute([$filename]);

        out("<strong>$filename</strong> — applied successfully", 'ok');
        $ran_count++;
    }
}

// ── Summary ───────────────────────────────────────────────────────────────────
echo PHP_EOL;
out("Done. Applied: <strong>$ran_count</strong> &nbsp;|&nbsp; Skipped: <strong>$skip_count</strong> &nbsp;|&nbsp; Errors: <strong>$err_count</strong>", $err_count > 0 ? 'error' : 'ok');

if (!$isCli): ?>
<hr class="mt-4">
<p class="text-muted small">Delete or password-protect this file after running. <a href="/admin">← Back to admin</a></p>
</div></body></html>
<?php endif;

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
