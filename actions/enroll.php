<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/courses.php');
    exit;
}

verify_csrf();

$courseId = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
$user = current_user();

if (!$courseId) {
    set_flash('error', 'Invalid course.');
    header('Location: /pages/courses.php');
    exit;
}

$course = get_course($courseId);
if (!$course || $course['status'] !== 'published') {
    set_flash('error', 'Course not found.');
    header('Location: /pages/courses.php');
    exit;
}

enroll_user($user['id'], $courseId);
set_flash('success', 'You have enrolled in "' . $course['title'] . '". Let\'s start learning!');
header('Location: /pages/course.php?id=' . $courseId);
exit;
