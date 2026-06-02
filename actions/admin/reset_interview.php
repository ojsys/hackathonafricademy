<?php
/**
 * Delete a candidate's interview session so they can start over.
 *
 * Removes the session row (which cascades to its answers, events and proctor
 * image records) and deletes the proctor snapshot files from disk. After this
 * the candidate has no session and can begin the interview again (if it is open).
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
$session   = $sessionId ? get_interview_session($sessionId) : null;

if (!$session) {
    set_flash('error', 'Interview session not found.');
    header('Location: /admin/interview_reviews.php');
    exit;
}

// Delete the session, its cascading rows, and its proctor image files.
interview_delete_session($sessionId);

set_flash('success', 'Interview deleted. The candidate can now take the interview again.');
header('Location: /admin/interview_reviews.php');
exit;
