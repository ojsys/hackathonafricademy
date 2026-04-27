<?php
/**
 * Migration v2 — run ONCE, then DELETE this file.
 * Adds video_path to lessons table + creates upload directory.
 * Safe: ALTER TABLE ADD COLUMN is ignored if column already exists.
 * Access: /migrate_v2.php?key=hackathon2026migratev2
 */

define('MIGRATION_PASSWORD', 'hackathon2026migratev2');

if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026migratev2 to the URL.</p>');
}

require_once __DIR__ . '/config/database.php';

$results = [];

function runv2(string $label, string $sql): void {
    global $results;
    try {
        db()->exec($sql);
        $results[] = ['ok' => true, 'label' => $label];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        $harmless = stripos($msg, 'duplicate column') !== false
                 || stripos($msg, 'already exists') !== false;
        $results[] = ['ok' => $harmless, 'label' => $label, 'note' => $msg];
    }
}

// Add video_path column to lessons
runv2('lessons: add video_path', "ALTER TABLE lessons ADD COLUMN video_path TEXT DEFAULT NULL");
runv2('users: add gender', "ALTER TABLE users ADD COLUMN gender VARCHAR(20) DEFAULT NULL");

// Create upload directory
$uploadDir = __DIR__ . '/public/videos/lessons';
if (!is_dir($uploadDir)) {
    if (@mkdir($uploadDir, 0755, true)) {
        $results[] = ['ok' => true, 'label' => 'Created public/videos/lessons/ directory'];
    } else {
        $results[] = ['ok' => false, 'label' => 'Failed to create public/videos/lessons/ — create it manually and set permissions to 755'];
    }
} else {
    $results[] = ['ok' => true, 'label' => 'public/videos/lessons/ already exists'];
}

$allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Migration v2</title>
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
<h2>Migration v2 Result</h2>
<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '✓ All steps completed.' : '⚠ Some steps had issues — review below.' ?>
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
