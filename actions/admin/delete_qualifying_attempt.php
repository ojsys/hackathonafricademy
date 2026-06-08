<?php
/**
 * Delete a candidate's qualifying-exam attempt so they can retake.
 *
 * Removes the attempt (cascading to its proctor sessions/images and deleting
 * the snapshot files), then recomputes the candidate review so eligibility,
 * scores and status reflect the removal.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$attemptId = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);

if (!$attemptId) {
    set_flash('error', 'Invalid qualifying exam attempt.');
    header('Location: /admin/candidates.php');
    exit;
}

// Resolve the owning user before deleting so we can recompute their review.
$stmt = db()->prepare('SELECT user_id FROM qualifying_attempts WHERE id = ?');
$stmt->execute([$attemptId]);
$userId = $stmt->fetchColumn();

if ($userId === false) {
    set_flash('error', 'Qualifying exam attempt not found.');
    header('Location: /admin/candidates.php');
    exit;
}

qualifying_delete_attempt($attemptId);

create_or_update_candidate_review((int)$userId);

set_flash('success', 'Qualifying exam attempt deleted. The candidate can now retake it.');
header('Location: /admin/candidates.php');
exit;
