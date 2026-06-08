<?php
/**
 * Delete a candidate's final-exam attempt so they can retake.
 *
 * Removes the attempt row and its proctoring events, then recomputes the
 * candidate review (scores, attempt penalty, eligibility). After this the
 * student can sit the exam again — and if the deleted attempt was their only
 * passing one, the "already passed" retake block is lifted.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$attemptId = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);

if (!$attemptId) {
    set_flash('error', 'Invalid exam attempt.');
    header('Location: /admin/candidates.php');
    exit;
}

// Look up the owning user before deleting so we can recompute their review.
$stmt = db()->prepare('SELECT user_id FROM final_exam_attempts WHERE id = ?');
$stmt->execute([$attemptId]);
$userId = $stmt->fetchColumn();

if ($userId === false) {
    set_flash('error', 'Exam attempt not found.');
    header('Location: /admin/candidates.php');
    exit;
}

// Remove the attempt's integrity events (no FK cascade on attempt_id).
// Guarded so the attempt can still be deleted on a server where the
// final_exam_events table has not been created yet (migrate_v4).
try {
    db()->prepare('DELETE FROM final_exam_events WHERE attempt_id = ?')->execute([$attemptId]);
} catch (PDOException $e) {
    // table missing — nothing to clean up
}

db()->prepare('DELETE FROM final_exam_attempts WHERE id = ?')->execute([$attemptId]);

// Recompute scores, attempt penalty and eligibility for this candidate.
create_or_update_candidate_review((int)$userId);

set_flash('success', 'Exam attempt deleted. The candidate can now retake the exam.');
header('Location: /admin/candidates.php');
exit;
