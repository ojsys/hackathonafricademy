<?php
/**
 * Migration v8 — Attach verified YouTube videos to the Python course lessons.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v8.php?key=hackathon2026pyvideos
 *
 * DATA-SAFE: only runs narrow UPDATEs that set lessons.video_url for specific
 * lessons in the "Python: From Zero to Developer" course, matched by course +
 * lesson title. It does NOT delete/insert anything, does NOT touch lesson IDs,
 * student progress, other lessons, or other courses. Idempotent: re-running
 * just re-applies the same URLs.
 *
 * Each URL has been verified live and embeddable via YouTube's oEmbed endpoint.
 * Only embed-form URLs (youtube.com/embed/ID) are used, as pages/lesson.php
 * renders video_url inside an <iframe>.
 *
 * Currently wires up: MODULE 1 (4 lessons). Later modules are added in v9+.
 */

define('MIGRATION_PASSWORD', 'hackathon2026pyvideos');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026pyvideos to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'Python: From Zero to Developer';

// lesson title  =>  YouTube EMBED url (verified live + embeddable)
$VIDEOS = [
    // ── Module 1: Python Fundamentals — Getting Started ──
    'What is Python and how does it run?'
        => 'https://www.youtube.com/embed/BkHdmAhapws',   // Afternerd — What is the Python Interpreter?
    'Installing Python and your first program'
        => 'https://www.youtube.com/embed/D2cwvpJSBX4',   // Visual Studio Code (official) — Getting Started with Python in VS Code
    'Variables, objects, and dynamic typing'
        => 'https://www.youtube.com/embed/cQT33yu9pY8',   // Programming with Mosh — Python Variables
    'Comments, the REPL workflow, and the Zen of Python'
        => 'https://www.youtube.com/embed/uBHOb55-fBo',   // Indian Pythonista — The Zen of Python, decoded.
];

echo '<h2>Attaching videos to: ' . htmlspecialchars($COURSE_TITLE) . '</h2>';

// Find the course (must exist — seeded by migrate_v7)
$stmt = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
$stmt->execute([$COURSE_TITLE]);
$courseId = $stmt->fetchColumn();

if (!$courseId) {
    http_response_code(404);
    die('<h3 style="color:red">Course not found. Run migrate_v7.php first.</h3>');
}

$pdo->beginTransaction();
try {
    // Narrow, guarded UPDATE: only this course's lessons, only video_url.
    $upd = $pdo->prepare(
        'UPDATE lessons SET video_url = ?
         WHERE title = ?
           AND module_id IN (SELECT id FROM modules WHERE course_id = ?)'
    );

    $updated = 0; $missing = [];
    foreach ($VIDEOS as $lessonTitle => $embedUrl) {
        $upd->execute([$embedUrl, $lessonTitle, $courseId]);
        if ($upd->rowCount() > 0) {
            $updated += $upd->rowCount();
            echo '<p>&#9989; ' . htmlspecialchars($lessonTitle) . ' &rarr; ' . htmlspecialchars($embedUrl) . '</p>';
        } else {
            // rowCount() can be 0 on SQLite if the value is unchanged (re-run).
            // Distinguish "already set" from "lesson not found".
            $check = $pdo->prepare(
                'SELECT video_url FROM lessons
                 WHERE title = ? AND module_id IN (SELECT id FROM modules WHERE course_id = ?)'
            );
            $check->execute([$lessonTitle, $courseId]);
            $current = $check->fetchColumn();
            if ($current === false) {
                $missing[] = $lessonTitle;
                echo '<p style="color:#b00">&#9888; Lesson not found: ' . htmlspecialchars($lessonTitle) . '</p>';
            } else {
                echo '<p>&#8505;&#65039; Already set: ' . htmlspecialchars($lessonTitle) . '</p>';
            }
        }
    }

    $pdo->commit();
    echo '<h3 style="color:green">Done. Applied ' . count($VIDEOS) . ' video link(s).</h3>';
    if ($missing) {
        echo '<p style="color:#b00">Check the titles above — these did not match any lesson: '
            . htmlspecialchars(implode('; ', $missing)) . '</p>';
    }
    echo '<p><strong>Now DELETE this file from the server.</strong></p>';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo '<h3 style="color:red">Failed: ' . htmlspecialchars($e->getMessage()) . '</h3><p>No changes were saved.</p>';
}
