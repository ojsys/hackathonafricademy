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

// Randomly draw the required number of coding + debugging problems.
function draw_interview_ids(string $kind, int $count): array {
    $stmt = db()->prepare('SELECT id FROM interview_exercises WHERE kind = ? AND is_active = 1');
    $stmt->execute([$kind]);
    $ids = array_map('intval', array_column($stmt->fetchAll(), 'id'));
    shuffle($ids);
    return array_slice($ids, 0, $count);
}

$codingIds = draw_interview_ids('coding', INTERVIEW_CODING_COUNT);
$debugIds  = draw_interview_ids('debugging', INTERVIEW_DEBUG_COUNT);

if (count($codingIds) < INTERVIEW_CODING_COUNT || count($debugIds) < INTERVIEW_DEBUG_COUNT) {
    set_flash('error', 'The interview is not fully configured yet. Please contact the administrators.');
    header('Location: /pages/interview.php');
    exit;
}

// Merge and shuffle the full set so order also differs between candidates.
$selected = array_merge($codingIds, $debugIds);
shuffle($selected);

// Total points available across the chosen set (for the max score).
$place    = implode(',', array_fill(0, count($selected), '?'));
$ptsStmt  = db()->prepare("SELECT COALESCE(SUM(points),0) FROM interview_exercises WHERE id IN ($place)");
$ptsStmt->execute($selected);
$maxPoints = (int)$ptsStmt->fetchColumn();

$stmt = db()->prepare(
    'INSERT INTO interview_sessions (user_id, status, exercise_ids_json, time_limit, max_points, started_at)
     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
);
$stmt->execute([$user['id'], 'in_progress', json_encode($selected), INTERVIEW_TIME_LIMIT, $maxPoints]);

header('Location: /pages/interview_take.php');
exit;
