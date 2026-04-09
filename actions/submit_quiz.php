<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/courses.php');
    exit;
}

verify_csrf();

$quizId  = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);
$answers = $_POST['answers'] ?? [];
$user = current_user();

if (!$quizId || empty($answers)) {
    set_flash('error', 'Please answer all questions before submitting.');
    header('Location: /pages/quiz.php?id=' . (int)$quizId);
    exit;
}

$quiz = get_quiz($quizId);
if (!$quiz) {
    header('Location: /pages/courses.php');
    exit;
}

$questions = get_questions_for_quiz($quizId);
$total = count($questions);
$correct = 0;

foreach ($questions as $q) {
    $selectedOptionId = isset($answers[$q['id']]) ? (int)$answers[$q['id']] : 0;
    foreach ($q['options'] as $opt) {
        if ((int)$opt['id'] === $selectedOptionId && $opt['is_correct']) {
            $correct++;
            break;
        }
    }
}

$score  = $total > 0 ? (int)round(($correct / $total) * 100) : 0;
$passed = $score >= $quiz['pass_mark'];

// Record attempt
$stmt = db()->prepare('INSERT INTO quiz_attempts (user_id, quiz_id, score, passed) VALUES (?, ?, ?, ?)');
$stmt->execute([$user['id'], $quizId, $score, $passed ? 1 : 0]);

// Store result in session for display
start_session();
$_SESSION['quiz_result'] = [
    'quiz_id' => $quizId,
    'score'   => $score,
    'passed'  => $passed,
    'correct' => $correct,
    'total'   => $total,
];

header('Location: /pages/quiz.php?id=' . $quizId);
exit;
