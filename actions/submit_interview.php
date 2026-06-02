<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$auto = isset($_GET['auto']) || isset($_POST['auto']);

// Auto-submit (timer expiry) may arrive as a GET redirect without CSRF; a
// manual submit is a POST and must carry a valid token.
if (!$auto) {
    verify_csrf();
}

$session = get_interview_session_for_user($user['id']);
if (!$session) { header('Location: /pages/interview.php'); exit; }

if ($session['status'] !== 'in_progress') {
    // Already finalised — just show the status page.
    header('Location: /pages/interview_result.php');
    exit;
}

// Advisory auto-score from the sample tests the candidate ran (NOT authoritative;
// the admin runs the full hidden suite at review time).
$orderedIds  = json_decode($session['exercise_ids_json'] ?? '[]', true) ?: [];
$exercises   = get_interview_exercises_in_order(array_map('intval', $orderedIds));
$answers     = get_interview_answers((int)$session['id']);

$sampleTotal = 0; $samplePassed = 0;
foreach ($exercises as $ex) {
    $tc = json_decode($ex['test_cases_json'] ?? '{}', true) ?: [];
    $n  = count(array_filter($tc['cases'] ?? [], fn($c) => !empty($c['sample'])));
    $sampleTotal += $n;
    $a = $answers[(int)$ex['id']] ?? null;
    if ($a) $samplePassed += min($n, (int)$a['sample_passed']);
}
$autoScore = $sampleTotal > 0 ? (int)round($samplePassed / $sampleTotal * 100) : 0;

db()->prepare(
    "UPDATE interview_sessions
        SET status = 'submitted', submitted_at = CURRENT_TIMESTAMP, auto_score = ?
      WHERE id = ? AND status = 'in_progress'"
)->execute([$autoScore, $session['id']]);

set_flash('success', 'Your coding interview has been submitted for review.');
header('Location: /pages/interview_result.php');
exit;
