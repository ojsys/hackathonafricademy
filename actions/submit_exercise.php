<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$user       = current_user();
$exerciseId = filter_input(INPUT_POST, 'exercise_id', FILTER_VALIDATE_INT);
$code       = trim($_POST['submitted_code'] ?? '');
$isCorrect  = ($_POST['is_correct'] ?? '0') === '1' ? 1 : 0;
$reqsMet    = max(0, (int)($_POST['reqs_met']   ?? 0));
$reqsTotal  = max(0, (int)($_POST['reqs_total'] ?? 0));
$score      = $reqsTotal > 0 ? (int)round($reqsMet / $reqsTotal * 100) : ($isCorrect ? 100 : 0);

if (!$exerciseId || $code === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$exercise = get_exercise($exerciseId);
if (!$exercise) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Exercise not found']);
    exit;
}

$existing = get_user_exercise_submission($user['id'], $exerciseId);

if ($existing) {
    $stmt = db()->prepare(
        'UPDATE code_submissions
         SET submitted_code = ?, is_correct = ?, score = ?, attempts = attempts + 1, submitted_at = CURRENT_TIMESTAMP
         WHERE id = ?'
    );
    $stmt->execute([$code, $isCorrect, $score, $existing['id']]);
} else {
    $stmt = db()->prepare(
        'INSERT INTO code_submissions (user_id, exercise_id, submitted_code, is_correct, score, attempts)
         VALUES (?, ?, ?, ?, ?, 1)'
    );
    $stmt->execute([$user['id'], $exerciseId, $code, $isCorrect, $score]);
}

echo json_encode([
    'success'    => true,
    'is_correct' => (bool)$isCorrect,
    'score'      => $score,
    'message'    => $isCorrect ? 'Exercise completed!' : 'Progress saved.',
]);
