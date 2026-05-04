<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$auto = isset($_GET['auto']);

if ($auto) {
    $attemptId = filter_input(INPUT_GET, 'attempt_id', FILTER_VALIDATE_INT);
} else {
    verify_csrf();
    $attemptId = filter_input(INPUT_POST, 'attempt_id', FILTER_VALIDATE_INT);
}

// Load and validate attempt
$attempt = get_qualifying_attempt($attemptId);
if (!$attempt || $attempt['user_id'] !== $user['id'] || $attempt['completed_at'] !== null) {
    header('Location: /pages/qualifying_exam.php');
    exit;
}

$exam = db()->prepare('SELECT * FROM qualifying_exam WHERE id = ?');
$exam->execute([$attempt['exam_id']]);
$exam = $exam->fetch();

// Grade only the questions assigned to this attempt
$assignedIds = json_decode($attempt['question_ids_json'] ?? '[]', true);
if (!empty($assignedIds)) {
    $placeholders = implode(',', array_fill(0, count($assignedIds), '?'));
    $stmt = db()->prepare("SELECT * FROM qualifying_questions WHERE id IN ($placeholders)");
    $stmt->execute($assignedIds);
    $questions = $stmt->fetchAll();
} else {
    // Fallback for legacy attempts without assigned question set
    $questions = get_qualifying_questions($exam['id']);
}

// Grade answers
$totalPoints = 0;
$earnedPoints = 0;
$answers = [];

foreach ($questions as $q) {
    $totalPoints += (int)$q['points'];
    $correctAnswer = (string)$q['correct_answer'];
    $options = json_decode($q['options_json'], true) ?? [];

    $userAnswer = null;
    if (!$auto) {
        $key = 'answer_' . $q['id'];
        $raw = $_POST[$key] ?? null;
        if ($raw !== null) $userAnswer = (string)(int)$raw;
    }

    $isCorrect = $userAnswer !== null && $userAnswer === $correctAnswer;
    if ($isCorrect) $earnedPoints += (int)$q['points'];

    $answers[$q['id']] = [
        'user'    => $userAnswer,
        'correct' => $correctAnswer,
        'right'   => $isCorrect,
        'text'    => $q['question_text'],
        'options' => $options,
    ];
}

$percentage  = $totalPoints > 0 ? (int)round($earnedPoints / $totalPoints * 100) : 0;
$passed      = $percentage >= (int)$exam['pass_mark'] ? 1 : 0;

$timeTaken = $auto ? null : filter_input(INPUT_POST, 'time_taken', FILTER_VALIDATE_INT);

// Save result
$stmt = db()->prepare('
    UPDATE qualifying_attempts
    SET score = ?, total_points = ?, percentage = ?, passed = ?, time_taken = ?,
        answers_json = ?, completed_at = CURRENT_TIMESTAMP
    WHERE id = ?
');
$stmt->execute([
    $earnedPoints, $totalPoints, $percentage, $passed,
    $timeTaken, json_encode($answers), $attemptId
]);

// Close proctor session
db()->prepare('UPDATE proctor_sessions SET ended_at = CURRENT_TIMESTAMP WHERE attempt_id = ?')
    ->execute([$attemptId]);

// Save qualifying score to candidate_reviews first
$review = get_candidate_review($user['id']);
if ($review) {
    db()->prepare('UPDATE candidate_reviews SET qualifying_score = ?, qualifying_passed = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?')
        ->execute([$percentage, $passed, $user['id']]);
} else {
    db()->prepare('INSERT INTO candidate_reviews (user_id, qualifying_score, qualifying_passed) VALUES (?, ?, ?)')
        ->execute([$user['id'], $percentage, $passed]);
}

// Recalculate full eligibility now that the qualifying result is recorded.
// This handles both outcomes:
//   pass  → promoted to 'eligible' if composite >= 75 and all courses done
//   fail  → demoted to 'to_be_decided' regardless of course scores
create_or_update_candidate_review($user['id']);


header('Location: /pages/qualifying_result.php?attempt_id=' . $attemptId);
exit;
