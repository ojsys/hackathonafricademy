<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: [];

start_session();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'] ?? '')) {
    http_response_code(403); echo json_encode(['ok' => false, 'error' => 'invalid_token']); exit;
}

$user      = current_user();
$sessionId = (int)($data['session_id'] ?? 0);
$exerciseId= (int)($data['exercise_id'] ?? 0);
$code      = (string)($data['code'] ?? '');

// Verify the session belongs to this user and is still open.
$session = get_interview_session($sessionId);
if (!$session || (int)$session['user_id'] !== (int)$user['id'] || $session['status'] !== 'in_progress') {
    http_response_code(403); echo json_encode(['ok' => false, 'error' => 'invalid_session']); exit;
}

// Verify the exercise is actually part of this session's frozen set.
$orderedIds = json_decode($session['exercise_ids_json'] ?? '[]', true) ?: [];
if (!in_array($exerciseId, array_map('intval', $orderedIds), true)) {
    http_response_code(400); echo json_encode(['ok' => false, 'error' => 'not_in_session']); exit;
}

$ex = get_interview_exercise($exerciseId);
if (!$ex) { http_response_code(404); echo json_encode(['ok' => false, 'error' => 'no_exercise']); exit; }

// Sample results are sent only when the candidate runs the tests. On a plain
// autosave they are absent — pass NULL so the existing values are preserved
// (COALESCE) instead of being wiped. Self-reported counts are advisory and
// clamped to the real sample count.
$tc          = json_decode($ex['test_cases_json'] ?? '{}', true) ?: [];
$sampleTotal = count(array_filter($tc['cases'] ?? [], fn($c) => !empty($c['sample'])));
$hasSample   = isset($data['sample_passed']) && isset($data['sample_total']);
$reportPass  = $hasSample ? max(0, min($sampleTotal, (int)$data['sample_passed'])) : null;
$reportTotal = $hasSample ? $sampleTotal : null;

// Upsert the answer (UNIQUE(session_id, exercise_id)).
$stmt = db()->prepare(
    'INSERT INTO interview_answers (session_id, user_id, exercise_id, kind, submitted_code, sample_passed, sample_total, updated_at)
     VALUES (:sid, :uid, :eid, :kind, :code, COALESCE(:sp, 0), COALESCE(:st, 0), CURRENT_TIMESTAMP)
     ON CONFLICT(session_id, exercise_id) DO UPDATE SET
        submitted_code = :code,
        sample_passed  = COALESCE(:sp, sample_passed),
        sample_total   = COALESCE(:st, sample_total),
        updated_at     = CURRENT_TIMESTAMP'
);
$stmt->execute([
    ':sid' => $sessionId, ':uid' => $user['id'], ':eid' => $exerciseId, ':kind' => $ex['kind'],
    ':code' => $code, ':sp' => $reportPass, ':st' => $reportTotal,
]);

echo json_encode(['ok' => true]);
