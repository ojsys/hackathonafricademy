<?php
/**
 * Save an admin's review of a coding interview session: per-task scores +
 * the overall selection decision. Marks the session 'reviewed'.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$admin     = current_user();
$sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

$session = $sessionId ? get_interview_session($sessionId) : null;
if (!$session || $session['status'] === 'in_progress') {
    set_flash('error', 'Cannot review an interview that has not been submitted.');
    header('Location: /admin/interview_reviews.php');
    exit;
}

$decision = $_POST['decision'] ?? 'pending';
if (!in_array($decision, ['selected', 'rejected', 'pending'], true)) $decision = 'pending';
$notes = trim((string)($_POST['reviewer_notes'] ?? ''));

$orderedIds = json_decode($session['exercise_ids_json'] ?? '[]', true) ?: [];
$exercises  = get_interview_exercises_in_order(array_map('intval', $orderedIds));

$upd = db()->prepare(
    'UPDATE interview_answers
        SET admin_score = :score, admin_passed = :passed, admin_total = :total,
            admin_feedback = :fb, updated_at = CURRENT_TIMESTAMP
      WHERE session_id = :sid AND exercise_id = :eid'
);

foreach ($exercises as $ex) {
    $eid    = (int)$ex['id'];
    $maxPts = (int)$ex['points'];
    $rawScore = $_POST['score_' . $eid] ?? '';
    $score  = $rawScore === '' ? null : max(0, min($maxPts, (int)$rawScore));
    $passed = ($_POST['passed_' . $eid] ?? '') === '' ? null : (int)$_POST['passed_' . $eid];
    $total  = ($_POST['total_' . $eid] ?? '') === '' ? null : (int)$_POST['total_' . $eid];
    $fb     = trim((string)($_POST['feedback_' . $eid] ?? ''));

    // Ensure a row exists even if the candidate never opened this task.
    db()->prepare(
        'INSERT OR IGNORE INTO interview_answers (session_id, user_id, exercise_id, kind, submitted_code)
         VALUES (?, ?, ?, ?, ?)'
    )->execute([$sessionId, $session['user_id'], $eid, $ex['kind'], '']);

    $upd->execute([
        ':score' => $score, ':passed' => $passed, ':total' => $total,
        ':fb' => $fb, ':sid' => $sessionId, ':eid' => $eid,
    ]);
}

db()->prepare(
    "UPDATE interview_sessions
        SET status = 'reviewed', review_decision = ?, reviewer_id = ?, reviewer_notes = ?, reviewed_at = CURRENT_TIMESTAMP
      WHERE id = ?"
)->execute([$decision, $admin['id'], $notes, $sessionId]);

set_flash('success', 'Review saved.');
header('Location: /admin/interview_reviews.php?session_id=' . $sessionId);
exit;
