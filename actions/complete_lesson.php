<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/courses.php');
    exit;
}

verify_csrf();

$lessonId = filter_input(INPUT_POST, 'lesson_id', FILTER_VALIDATE_INT);
$redirect = $_POST['redirect'] ?? '/pages/dashboard.php';
$user = current_user();

if (!$lessonId) {
    set_flash('error', 'Invalid lesson.');
    header('Location: ' . $redirect);
    exit;
}

$lesson = get_lesson($lessonId);
if (!$lesson) {
    set_flash('error', 'Lesson not found.');
    header('Location: /pages/dashboard.php');
    exit;
}

// Ensure enrolled
if (!is_enrolled($user['id'], $lesson['course_id'])) {
    enroll_user($user['id'], $lesson['course_id']);
}

// Mark lesson as complete (ignore duplicates)
$ignore = DB_DRIVER === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
$stmt = db()->prepare("INSERT $ignore INTO user_lesson_progress (user_id, lesson_id) VALUES (?, ?)");
$stmt->execute([$user['id'], $lessonId]);

set_flash('success', 'Lesson marked as complete! Keep going.');

// Only allow relative redirect
if (strpos($redirect, '/pages/') !== 0 && strpos($redirect, '/admin/') !== 0) {
    $redirect = '/pages/dashboard.php';
}
header('Location: ' . $redirect);
exit;
