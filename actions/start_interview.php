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

// Randomly draw problems of a given kind + difficulty.
function draw_interview_ids(string $kind, string $difficulty, int $count): array {
    $stmt = db()->prepare('SELECT id FROM interview_exercises WHERE kind = ? AND difficulty = ? AND is_active = 1');
    $stmt->execute([$kind, $difficulty]);
    $ids = array_map('intval', array_column($stmt->fetchAll(), 'id'));
    shuffle($ids);
    return array_slice($ids, 0, $count);
}

// Draw an easy/medium quota for a kind, topping up from the rest of that kind
// if a difficulty bucket is short, so the session is always complete.
function draw_interview_set(string $kind, int $easyN, int $medN, int $total): array {
    $picked = array_merge(
        draw_interview_ids($kind, 'easy', $easyN),
        draw_interview_ids($kind, 'medium', $medN)
    );
    if (count($picked) < $total) {
        $stmt = db()->prepare('SELECT id FROM interview_exercises WHERE kind = ? AND is_active = 1');
        $stmt->execute([$kind]);
        $rest = array_values(array_diff(array_map('intval', array_column($stmt->fetchAll(), 'id')), $picked));
        shuffle($rest);
        $picked = array_merge($picked, array_slice($rest, 0, $total - count($picked)));
    }
    return array_slice($picked, 0, $total);
}

// 70% easy / 30% medium across the 13 tasks: 2E+1M coding, 7E+3M debugging.
$codingIds = draw_interview_set('coding', 2, 1, INTERVIEW_CODING_COUNT);
$debugIds  = draw_interview_set('debugging', 7, 3, INTERVIEW_DEBUG_COUNT);

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
