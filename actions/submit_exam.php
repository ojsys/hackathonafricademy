<?php
/**
 * Submit Final Exam — grades MCQ automatically, stores coding answers for review
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();
verify_csrf();

$user = current_user();
$examId = filter_input(INPUT_POST, 'exam_id', FILTER_VALIDATE_INT);
$timeTaken = filter_input(INPUT_POST, 'time_taken', FILTER_VALIDATE_INT) ?? 0;

if (!$examId) {
    set_flash('error', 'Invalid exam submission.');
    header('Location: /pages/courses.php');
    exit;
}

// Get exam and questions
$exam = null;
$stmt = db()->prepare('SELECT fe.*, c.id as course_id FROM final_exams fe JOIN courses c ON c.id = fe.course_id WHERE fe.id = ?');
$stmt->execute([$examId]);
$exam = $stmt->fetch();

if (!$exam) {
    set_flash('error', 'Exam not found.');
    header('Location: /pages/courses.php');
    exit;
}

$questions = get_final_exam_questions($examId);
$totalPoints = 0;
$earnedPoints = 0;
$mcqScore = 0;
$codingScore = 0;
$mcqTotal = 0;
$codingTotal = 0;
$answers = [];
$codeSubmissions = [];

foreach ($questions as $q) {
    $totalPoints += $q['points'];

    if ($q['question_type'] === 'mcq') {
        $mcqTotal += $q['points'];
        $userAnswer = $_POST['answer_' . $q['id']] ?? null;
        $correctAnswer = $q['correct_answer'];
        $isCorrect = ($userAnswer !== null && (string)$userAnswer === (string)$correctAnswer);

        $answers[$q['id']] = [
            'selected' => $userAnswer,
            'correct' => $correctAnswer,
            'is_correct' => $isCorrect
        ];

        if ($isCorrect) {
            $earnedPoints += $q['points'];
            $mcqScore += $q['points'];
        }
    } else {
        // Coding question — store submission, award partial credit if code is non-empty
        $codingTotal += $q['points'];
        $userCode = trim($_POST['code_' . $q['id']] ?? '');
        $starterCode = trim($q['starter_code'] ?? '');
        
        // Simple check: give points if student wrote substantial code beyond starter
        $hasCode = !empty($userCode) && $userCode !== $starterCode && strlen($userCode) > strlen($starterCode) + 10;

        $codeSubmissions[$q['id']] = [
            'code' => $userCode,
            'has_content' => $hasCode
        ];

        if ($hasCode) {
            // Award full points for coding (manual review can adjust later)
            $earnedPoints += $q['points'];
            $codingScore += $q['points'];
        }
    }
}

$scorePercent = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
$mcqPercent = $mcqTotal > 0 ? round(($mcqScore / $mcqTotal) * 100) : 0;
$codingPercent = $codingTotal > 0 ? round(($codingScore / $codingTotal) * 100) : 0;
$passed = $scorePercent >= $exam['pass_mark'];

// Store attempt
$stmt = db()->prepare('
    INSERT INTO final_exam_attempts 
    (user_id, exam_id, score, mcq_score, coding_score, passed, time_taken, answers_json, code_submissions_json, completed_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
');
$stmt->execute([
    $user['id'],
    $examId,
    $scorePercent,
    $mcqPercent,
    $codingPercent,
    $passed ? 1 : 0,
    $timeTaken,
    json_encode($answers),
    json_encode($codeSubmissions)
]);

// Update candidate review if passed
if ($passed) {
    create_or_update_candidate_review($user['id']);
}

// Redirect to results
$_SESSION['exam_result'] = [
    'exam_id' => $examId,
    'course_id' => $exam['course_id'],
    'score' => $scorePercent,
    'mcq_score' => $mcqPercent,
    'coding_score' => $codingPercent,
    'passed' => $passed,
    'pass_mark' => $exam['pass_mark'],
    'total_questions' => count($questions),
    'answers' => $answers,
    'time_taken' => $timeTaken
];

header('Location: /pages/exam_result.php');
exit;
