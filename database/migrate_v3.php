<?php
/**
 * Migration v3 — run ONCE, then DELETE this file.
 * Raises the final-exam pass mark from 70% to 80%.
 *
 * SAFE: only updates the `final_exams` config table (3 rows). It does NOT
 * touch any student accounts, exam attempts, scores, or other live data, and
 * performs no DROP / schema change.
 * Idempotent: the WHERE guard means re-running changes nothing.
 * Works on SQLite and MySQL (uses the configured db() connection).
 *
 * Access: https://hackathonafricademy.com/database/migrate_v3.php?key=hackathon2026migratev3
 */

define('MIGRATION_PASSWORD', 'hackathon2026migratev3');

if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026migratev3 to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';

$results = [];
$after   = [];

try {
    // Apply the change (only rows still below 80 are touched).
    $stmt = db()->prepare('UPDATE final_exams SET pass_mark = 80 WHERE pass_mark < 80');
    $stmt->execute();
    $changed = $stmt->rowCount();

    // Read back the current state for confirmation.
    $after = db()->query('SELECT id, title, pass_mark FROM final_exams ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    $results[] = ['ok' => true, 'label' => "Final-exam pass marks set to 80% ($changed row(s) changed)"];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'label' => 'Update failed', 'note' => $e->getMessage()];
}

$allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Migration v3</title>
<style>
body{font-family:system-ui,sans-serif;max-width:700px;margin:2rem auto;padding:0 1rem}
.row{display:flex;align-items:flex-start;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #eee}
.icon{font-size:1.1rem;flex-shrink:0;margin-top:2px}
.ok{color:#16a34a}.fail{color:#dc2626}
.note{font-size:.8rem;color:#6b7280;margin-top:.2rem}
.banner{padding:1rem 1.25rem;border-radius:6px;margin-bottom:1.5rem;font-weight:600}
.banner.ok{background:#dcfce7;color:#15803d}
.banner.fail{background:#fee2e2;color:#b91c1c}
table{width:100%;border-collapse:collapse;margin-top:1.5rem;font-size:.9rem}
th,td{text-align:left;padding:.4rem .6rem;border-bottom:1px solid #eee}
.warn{background:#fef9c3;color:#854d0e;padding:.75rem 1rem;border-radius:6px;margin-top:1.5rem;font-size:.875rem}
</style>
</head>
<body>
<h2>Migration v3 Result</h2>
<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '✓ Completed.' : '⚠ Failed — review below.' ?>
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

<?php if (!empty($after)): ?>
<table>
    <tr><th>ID</th><th>Final Exam</th><th>Pass Mark</th></tr>
    <?php foreach ($after as $row): ?>
    <tr>
        <td><?= htmlspecialchars((string)$row['id']) ?></td>
        <td><?= htmlspecialchars((string)$row['title']) ?></td>
        <td><strong><?= htmlspecialchars((string)$row['pass_mark']) ?>%</strong></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<div class="warn">
    ⚠ <strong>Delete this file from the server immediately after running it.</strong>
</div>
</body>
</html>
