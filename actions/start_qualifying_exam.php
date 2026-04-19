<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
verify_csrf();

$user = current_user();
$examId = filter_input(INPUT_POST, 'exam_id', FILTER_VALIDATE_INT);

$exam = get_qualifying_exam();
if (!$exam || $exam['id'] !== $examId) {
    set_flash('error', 'Invalid exam.');
    header('Location: /pages/qualifying_exam.php');
    exit;
}

if (!is_eligible($user['id'])) {
    set_flash('error', 'You must complete all courses before attempting the final exam.');
    header('Location: /pages/qualifying_exam.php');
    exit;
}

if (has_passed_qualifying_exam($user['id'])) {
    set_flash('info', 'You have already passed the final exam.');
    header('Location: /pages/qualifying_exam.php');
    exit;
}

// Check for existing active attempt
$active = get_active_qualifying_attempt($user['id']);
if ($active) {
    header('Location: /pages/qualifying_take.php');
    exit;
}

// Create new attempt
$stmt = db()->prepare('INSERT INTO qualifying_attempts (user_id, exam_id, started_at) VALUES (?, ?, CURRENT_TIMESTAMP)');
$stmt->execute([$user['id'], $examId]);
$attemptId = db()->lastInsertId();

// Create proctor session
$stmt = db()->prepare('INSERT INTO proctor_sessions (attempt_id, user_id) VALUES (?, ?)');
$stmt->execute([$attemptId, $user['id']]);

header('Location: /pages/qualifying_take.php');
exit;
