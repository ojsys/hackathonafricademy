<?php
/**
 * Migration v11 — Participant Feedback system (Feeding / Accommodation / Training).
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v11.php?key=hackathon2026feedback
 *
 * DATA-SAFE: only CREATE TABLE IF NOT EXISTS / CREATE INDEX IF NOT EXISTS plus a
 * single site_settings row. It does NOT drop, alter or delete anything, and does
 * not touch users, lessons, attempts or any existing data. Idempotent — re-running
 * is a no-op.
 *
 * Creates:
 *   feedback_responses  one row per submitted form
 *   feedback_answers    one row per answered question (multi-select joined by '; ')
 */

define('MIGRATION_PASSWORD', 'hackathon2026feedback');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026feedback to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
header('Content-Type: text/html; charset=utf-8');

echo '<h2>Migration v11 — Participant Feedback</h2>';

$isSqlite = DB_DRIVER === 'sqlite';

if ($isSqlite) {
    $ddl = [
        'feedback_responses' => "
            CREATE TABLE IF NOT EXISTS feedback_responses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category TEXT NOT NULL,
                user_id INTEGER,
                respondent_name VARCHAR(150),
                is_anonymous INTEGER NOT NULL DEFAULT 1,
                feedback_date DATE,
                submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )",
        'feedback_answers' => "
            CREATE TABLE IF NOT EXISTS feedback_answers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                response_id INTEGER NOT NULL,
                question_key VARCHAR(60) NOT NULL,
                answer_value TEXT,
                FOREIGN KEY (response_id) REFERENCES feedback_responses(id) ON DELETE CASCADE
            )",
    ];
    $indexes = [
        'CREATE INDEX IF NOT EXISTS idx_feedback_category ON feedback_responses(category)',
        'CREATE INDEX IF NOT EXISTS idx_feedback_submitted ON feedback_responses(submitted_at)',
        'CREATE INDEX IF NOT EXISTS idx_feedback_answers_response ON feedback_answers(response_id)',
        'CREATE INDEX IF NOT EXISTS idx_feedback_answers_key ON feedback_answers(question_key)',
    ];
} else {
    $ddl = [
        'feedback_responses' => "
            CREATE TABLE IF NOT EXISTS feedback_responses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category VARCHAR(30) NOT NULL,
                user_id INT NULL,
                respondent_name VARCHAR(150) NULL,
                is_anonymous TINYINT(1) NOT NULL DEFAULT 1,
                feedback_date DATE NULL,
                submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_feedback_category (category),
                INDEX idx_feedback_submitted (submitted_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'feedback_answers' => "
            CREATE TABLE IF NOT EXISTS feedback_answers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                response_id INT NOT NULL,
                question_key VARCHAR(60) NOT NULL,
                answer_value TEXT NULL,
                INDEX idx_feedback_answers_response (response_id),
                INDEX idx_feedback_answers_key (question_key),
                FOREIGN KEY (response_id) REFERENCES feedback_responses(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    $indexes = [];
}

try {
    foreach ($ddl as $table => $sql) {
        $pdo->exec($sql);
        echo '<p>&#9989; Table ready: <strong>' . htmlspecialchars($table) . '</strong></p>';
    }
    foreach ($indexes as $sql) {
        $pdo->exec($sql);
    }
    if ($indexes) echo '<p>&#9989; Indexes ready.</p>';

    // Feedback forms open by default (admin can close them from Settings).
    $check = $pdo->prepare('SELECT COUNT(*) FROM site_settings WHERE setting_key = ?');
    $check->execute(['feedback_open']);
    if ((int)$check->fetchColumn() === 0) {
        $ins = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        $ins->execute(['feedback_open', '1']);
        echo '<p>&#9989; Setting <strong>feedback_open</strong> = 1 (forms open).</p>';
    } else {
        echo '<p>&#8505;&#65039; Setting <strong>feedback_open</strong> already present — left untouched.</p>';
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM feedback_responses')->fetchColumn();
    echo '<h3 style="color:green">Done. ' . $count . ' feedback response(s) currently stored.</h3>';
    echo '<p>Participants: <a href="/pages/feedback.php">/pages/feedback.php</a> &nbsp;|&nbsp; '
       . 'Admin: <a href="/admin/feedback.php">/admin/feedback.php</a></p>';
    echo '<p><strong>Now DELETE this file from the server.</strong></p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h3 style="color:red">Failed: ' . htmlspecialchars($e->getMessage()) . '</h3>';
}
