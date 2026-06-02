<?php
/**
 * Let an admin take the interview as a test run WITHOUT opening it for
 * candidates. This bypasses the global open switch and the eligibility gate,
 * is repeatable (any previous admin session is cleared first), and flags the
 * session as a test so it stays out of the real candidate review queue.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$admin = current_user();

// Clear the admin's previous interview session (test or otherwise) so testing
// is always repeatable.
$existing = get_interview_session_for_user($admin['id']);
if ($existing) {
    interview_delete_session((int)$existing['id']);
}

$selected = interview_build_question_set();
if ($selected === null) {
    set_flash('error', 'The interview pool is not fully configured. Run the interview setup first.');
    header('Location: /admin/interview_reviews.php');
    exit;
}

interview_create_session($admin['id'], $selected, true);

header('Location: /pages/interview_take.php');
exit;
