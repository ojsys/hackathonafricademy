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
        // Coding question — grade against the reference solution (partial credit
        // proportional to how much of the solution the student actually reproduced).
        $codingTotal += $q['points'];
        $userCode = trim($_POST['code_' . $q['id']] ?? '');

        $grade = grade_coding_answer($userCode, $q['solution_code'] ?? null, $q['starter_code'] ?? null, $q['points']);

        $codeSubmissions[$q['id']] = [
            'code'     => $userCode,
            'earned'   => $grade['earned'],
            'points'   => $q['points'],
            'coverage' => round($grade['coverage'], 2),
        ];

        $earnedPoints += $grade['earned'];
        $codingScore  += $grade['earned'];
    }
}

$rawScore = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
$mcqPercent = $mcqTotal > 0 ? round(($mcqScore / $mcqTotal) * 100) : 0;
$codingPercent = $codingTotal > 0 ? round(($codingScore / $codingTotal) * 100) : 0;

// Attempt penalty: each retake beyond the first costs 5 points off this
// attempt's recorded score (capped at 20), discouraging brute-force retries.
$priorStmt = db()->prepare('SELECT COUNT(*) FROM final_exam_attempts WHERE user_id = ? AND exam_id = ? AND completed_at IS NOT NULL');
$priorStmt->execute([$user['id'], $examId]);
$priorAttempts = (int)$priorStmt->fetchColumn();
$attemptPenalty = min($priorAttempts * 5, 20);

$scorePercent = (int)max(0, $rawScore - $attemptPenalty);
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

$attemptId = (int)db()->lastInsertId();

// Bind any integrity events logged during this run (attempt_id was NULL while
// the exam was in progress) to the attempt that was just submitted.
db()->prepare('UPDATE final_exam_events SET attempt_id = ? WHERE user_id = ? AND exam_id = ? AND attempt_id IS NULL')
    ->execute([$attemptId, $user['id'], $examId]);

// Always update candidate review after an exam attempt
create_or_update_candidate_review($user['id']);

// If all 3 courses are now complete, send completion email
if (is_eligible($user['id'])) {
    try {
        require_once __DIR__ . '/../includes/mailer.php';
        $review = get_candidate_review($user['id']);
        $composite = $review ? (float)($review['composite_score'] ?? 0) : 0;
        email_all_courses_complete($user['email'], $user['name'], $composite);
    } catch (Throwable $e) {
        error_log('Completion email failed: ' . $e->getMessage());
    }
}

// Redirect to results
$_SESSION['exam_result'] = [
    'exam_id' => $examId,
    'course_id' => $exam['course_id'],
    'score' => $scorePercent,
    'raw_score' => $rawScore,
    'attempt_penalty' => $attemptPenalty,
    'attempt_number' => $priorAttempts + 1,
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
