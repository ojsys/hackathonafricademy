<?php
/**
 * Migration v5 — run ONCE, then DELETE this file.
 * Adds the proctor_events table used for Qualifying-Exam proctoring flags
 * (tab-switch, window-blur, copy, paste, camera_denied).
 *
 * SAFE: only CREATE TABLE IF NOT EXISTS + CREATE INDEX. Creates no rows and
 * touches no existing data. Idempotent — re-running changes nothing.
 * Works on SQLite (production) and MySQL.
 *
 * Access: https://hackathonafricademy.com/database/migrate_v5.php?key=hackathon2026migratev5
 */

define('MIGRATION_PASSWORD', 'hackathon2026migratev5');

if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026migratev5 to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';

$results = [];

if (DB_DRIVER === 'mysql') {
    $createTable = "CREATE TABLE IF NOT EXISTS proctor_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attempt_id INT NOT NULL,
        session_id INT NULL,
        user_id INT NOT NULL,
        event_type VARCHAR(40) NOT NULL,
        detail VARCHAR(300),
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
} else {
    $createTable = "CREATE TABLE IF NOT EXISTS proctor_events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        attempt_id INTEGER NOT NULL,
        session_id INTEGER,
        user_id INTEGER NOT NULL,
        event_type TEXT NOT NULL,
        detail TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )";
}

function step(string $label, string $sql): void {
    global $results;
    try {
        db()->exec($sql);
        $results[] = ['ok' => true, 'label' => $label];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        $harmless = stripos($msg, 'already exists') !== false || stripos($msg, 'duplicate') !== false;
        $results[] = ['ok' => $harmless, 'label' => $label, 'note' => $msg];
    }
}

step('Create table proctor_events', $createTable);
step('Create index on attempt_id', 'CREATE INDEX IF NOT EXISTS idx_proctor_events_attempt ON proctor_events(attempt_id)');

try {
    db()->query('SELECT COUNT(*) FROM proctor_events')->fetchColumn();
    $results[] = ['ok' => true, 'label' => 'Verified table is queryable'];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'label' => 'Verification failed', 'note' => $e->getMessage()];
}

$allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Migration v5</title>
<style>
body{font-family:system-ui,sans-serif;max-width:700px;margin:2rem auto;padding:0 1rem}
.row{display:flex;align-items:flex-start;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #eee}
.icon{font-size:1.1rem;flex-shrink:0;margin-top:2px}
.ok{color:#16a34a}.fail{color:#dc2626}
.note{font-size:.8rem;color:#6b7280;margin-top:.2rem}
.banner{padding:1rem 1.25rem;border-radius:6px;margin-bottom:1.5rem;font-weight:600}
.banner.ok{background:#dcfce7;color:#15803d}
.banner.fail{background:#fee2e2;color:#b91c1c}
.warn{background:#fef9c3;color:#854d0e;padding:.75rem 1rem;border-radius:6px;margin-top:1.5rem;font-size:.875rem}
</style>
</head>
<body>
<h2>Migration v5 Result</h2>
<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '✓ Completed.' : '⚠ Some steps had issues — review below.' ?>
</div>
<?php foreach ($results as $r): ?>
<div class="row">
    <span class="icon <?= $r['ok'] ? 'ok' : 'fail' ?>"><?= $r['ok'] ? '✓' : '✗' ?></span>
    <div>
        <div><?= htmlspecialchars($r['label']) ?></div>
        <?php if (!empty($r['note'])): ?>
        <div class="note"><?= htmlspecialchars($r['note']) ?></div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<div class="warn">
    ⚠ <strong>Delete this file from the server immediately after running it.</strong>
</div>
</body>
</html>
