<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
verify_csrf();

$user = current_user();

// Gate: must have passed the qualifying/final exam (admins bypass).
if (!is_interview_unlocked($user['id']) && !is_admin()) {
    set_flash('error', 'You must pass the Final Exam before the coding interview unlocks.');
    header('Location: /pages/interview.php');
    exit;
}

// Global gate: the interview is closed until an admin opens it (admins may test).
if (!is_interview_open() && !is_admin()) {
    set_flash('info', 'The coding interview is not open yet. Please check back when it has been opened.');
    header('Location: /pages/interview.php');
    exit;
}

// One attempt only. Resume if in progress; block if already submitted/reviewed.
$existing = get_interview_session_for_user($user['id']);
if ($existing) {
    if ($existing['status'] === 'in_progress') {
        header('Location: /pages/interview_take.php');
        exit;
    }
    set_flash('info', 'You have already taken the coding interview. It can only be attempted once.');
    header('Location: /pages/interview.php');
    exit;
}

// Build the randomized 2 coding + 5 debugging + 4 applied set (shared helper).
$selected = interview_build_question_set();
if ($selected === null) {
    set_flash('error', 'The interview is not fully configured yet. Please contact the administrators.');
    header('Location: /pages/interview.php');
    exit;
}

// Any admin-initiated run is flagged as a test session (kept out of the real
// candidate review queue).
interview_create_session($user['id'], $selected, is_admin());

header('Location: /pages/interview_take.php');
exit;
