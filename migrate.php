<?php
/**
 * One-time migration script — run once, then DELETE this file from the server.
 * Safe: only uses CREATE TABLE IF NOT EXISTS and ALTER TABLE ADD COLUMN.
 * No existing data is modified or deleted.
 */

// Simple protection — change this password before uploading
define('MIGRATION_PASSWORD', 'hackathon2026migrate');

if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=YOUR_PASSWORD to the URL.</p>');
}

require_once __DIR__ . '/config/database.php';

$results = [];

function run(string $label, string $sql): void {
    global $results;
    try {
        db()->exec($sql);
        $results[] = ['ok' => true, 'label' => $label];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // "duplicate column" errors on ALTER TABLE are harmless — column already exists
        $harmless = stripos($msg, 'duplicate column') !== false
                 || stripos($msg, 'already exists') !== false;
        $results[] = ['ok' => $harmless, 'label' => $label, 'note' => $msg];
    }
}

// ── New tables ──────────────────────────────────────────────────────────────

run('qualifying_exam table', "
    CREATE TABLE IF NOT EXISTS qualifying_exam (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL DEFAULT 'Final Exam',
        description TEXT,
        instructions TEXT,
        pass_mark INTEGER NOT NULL DEFAULT 70,
        time_limit INTEGER NOT NULL DEFAULT 90,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

run('qualifying_questions table', "
    CREATE TABLE IF NOT EXISTS qualifying_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_id INTEGER NOT NULL,
        course_tag TEXT,
        question_text TEXT NOT NULL,
        options_json TEXT NOT NULL,
        correct_answer TEXT NOT NULL,
        points INTEGER NOT NULL DEFAULT 1,
        order_index INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (exam_id) REFERENCES qualifying_exam(id) ON DELETE CASCADE
    )
");

run('qualifying_attempts table', "
    CREATE TABLE IF NOT EXISTS qualifying_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        exam_id INTEGER NOT NULL,
        score INTEGER NOT NULL DEFAULT 0,
        total_points INTEGER NOT NULL DEFAULT 0,
        percentage INTEGER NOT NULL DEFAULT 0,
        passed INTEGER NOT NULL DEFAULT 0,
        time_taken INTEGER,
        answers_json TEXT,
        started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (exam_id) REFERENCES qualifying_exam(id) ON DELETE CASCADE
    )
");

run('proctor_sessions table', "
    CREATE TABLE IF NOT EXISTS proctor_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        attempt_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        camera_granted INTEGER NOT NULL DEFAULT 0,
        started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ended_at DATETIME,
        FOREIGN KEY (attempt_id) REFERENCES qualifying_attempts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

run('proctor_images table', "
    CREATE TABLE IF NOT EXISTS proctor_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        attempt_id INTEGER NOT NULL,
        image_path TEXT NOT NULL,
        captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES proctor_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (attempt_id) REFERENCES qualifying_attempts(id) ON DELETE CASCADE
    )
");

run('coding_exercises table', "
    CREATE TABLE IF NOT EXISTS coding_exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lesson_id INTEGER NOT NULL,
        title VARCHAR(200) NOT NULL,
        instructions TEXT,
        exercise_type TEXT NOT NULL DEFAULT 'html',
        starter_code TEXT,
        solution_code TEXT,
        hints TEXT,
        order_index INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
    )
");

run('code_submissions table', "
    CREATE TABLE IF NOT EXISTS code_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        code TEXT NOT NULL,
        is_correct INTEGER NOT NULL DEFAULT 0,
        score INTEGER DEFAULT 0,
        feedback TEXT,
        submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (exercise_id) REFERENCES coding_exercises(id) ON DELETE CASCADE
    )
");

run('final_exams table', "
    CREATE TABLE IF NOT EXISTS final_exams (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        course_id INTEGER NOT NULL UNIQUE,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        pass_mark INTEGER NOT NULL DEFAULT 70,
        time_limit INTEGER DEFAULT 60,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )
");

run('final_exam_questions table', "
    CREATE TABLE IF NOT EXISTS final_exam_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        exam_id INTEGER NOT NULL,
        question_type TEXT NOT NULL DEFAULT 'mcq',
        question_text TEXT NOT NULL,
        options_json TEXT,
        correct_answer TEXT,
        starter_code TEXT,
        points INTEGER DEFAULT 10,
        order_index INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (exam_id) REFERENCES final_exams(id) ON DELETE CASCADE
    )
");

run('final_exam_attempts table', "
    CREATE TABLE IF NOT EXISTS final_exam_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        exam_id INTEGER NOT NULL,
        score INTEGER NOT NULL DEFAULT 0,
        mcq_score INTEGER DEFAULT 0,
        passed INTEGER NOT NULL DEFAULT 0,
        time_taken INTEGER,
        answers_json TEXT,
        started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (exam_id) REFERENCES final_exams(id) ON DELETE CASCADE
    )
");

run('candidate_reviews table', "
    CREATE TABLE IF NOT EXISTS candidate_reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        reviewed_by INTEGER,
        eligibility_status TEXT DEFAULT 'pending',
        courses_completed INTEGER DEFAULT 0,
        total_score DECIMAL(5,2) DEFAULT 0,
        avg_quiz_score DECIMAL(5,2) DEFAULT 0,
        avg_exam_score DECIMAL(5,2) DEFAULT 0,
        composite_score DECIMAL(5,2) DEFAULT 0,
        speed_bonus DECIMAL(5,2) DEFAULT 0,
        attempt_penalty DECIMAL(5,2) DEFAULT 0,
        qualifying_score INTEGER DEFAULT 0,
        qualifying_passed INTEGER DEFAULT 0,
        admin_notes TEXT,
        reviewed_at DATETIME,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

run('activity_log table', "
    CREATE TABLE IF NOT EXISTS activity_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        details TEXT,
        ip_address TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

run('site_settings table', "
    CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key TEXT NOT NULL UNIQUE,
        value TEXT,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

// ── Add missing columns to existing candidate_reviews (safe, ignored if already present) ──

foreach (['composite_score DECIMAL(5,2) DEFAULT 0',
          'speed_bonus DECIMAL(5,2) DEFAULT 0',
          'attempt_penalty DECIMAL(5,2) DEFAULT 0',
          'qualifying_score INTEGER DEFAULT 0',
          'qualifying_passed INTEGER DEFAULT 0'] as $col) {
    $name = explode(' ', $col)[0];
    run("candidate_reviews: add $name", "ALTER TABLE candidate_reviews ADD COLUMN $col");
}

// ── Create proctor image directory ──────────────────────────────────────────
$proctorDir = __DIR__ . '/public/img/proctor';
if (!is_dir($proctorDir)) {
    mkdir($proctorDir, 0755, true);
    $results[] = ['ok' => true, 'label' => 'Created public/img/proctor/ directory'];
} else {
    $results[] = ['ok' => true, 'label' => 'public/img/proctor/ already exists'];
}

// ── Output ──────────────────────────────────────────────────────────────────
$allOk = array_reduce($results, fn($c, $r) => $c && $r['ok'], true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Migration</title>
<style>
body{font-family:system-ui,sans-serif;max-width:700px;margin:2rem auto;padding:0 1rem}
h2{margin-bottom:1rem}
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
<h2>Migration Result</h2>
<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '✓ All steps completed successfully.' : '⚠ Some steps had issues — review below.' ?>
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
    ⚠ <strong>Delete this file from the server immediately after running it.</strong><br>
    It should not remain publicly accessible.
</div>
</body>
</html>
