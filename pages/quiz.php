<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$quizId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$quizId) { header('Location: /pages/courses.php'); exit; }

$quiz = get_quiz($quizId);
if (!$quiz) { header('Location: /pages/courses.php'); exit; }

// Get module & course
$stmt = db()->prepare('SELECT m.*, c.id AS course_id, c.title AS course_title FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?');
$stmt->execute([$quiz['module_id']]);
$module = $stmt->fetch();
if (!$module) { header('Location: /pages/courses.php'); exit; }

$courseId       = $module['course_id'];
$isAdminPreview = is_admin();
if (!is_enrolled($user['id'], $courseId) && !$isAdminPreview) {
    enroll_user($user['id'], $courseId);
}

$questions = get_questions_for_quiz($quizId);
$bestAttempt = get_best_quiz_attempt($user['id'], $quizId);
$passed = $bestAttempt && $bestAttempt['passed'];

// Check if there is a fresh result to show (from POST redirect)
$result = null;
if (isset($_SESSION['quiz_result']) && $_SESSION['quiz_result']['quiz_id'] == $quizId) {
    $result = $_SESSION['quiz_result'];
    unset($_SESSION['quiz_result']);
}

$pageTitle = $quiz['title'];
require_once __DIR__ . '/../includes/header.php';
$course = get_course($courseId);
?>

<div class="page-banner">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/pages/course.php?id=<?= $courseId ?>" style="color:rgba(255,255,255,0.7)"><?= h($course['title']) ?></a></li>
                <li class="breadcrumb-item active text-white">Quiz</li>
            </ol>
        </nav>
        <h1><?= h($quiz['title']) ?></h1>
        <p><?= h($module['title']) ?> &middot; Pass mark: <?= $quiz['pass_mark'] ?>% &middot; <?= count($questions) ?> questions</p>
    </div>
</div>

<div class="quiz-container">

    <?php if ($isAdminPreview): ?>
    <div class="alert alert-warning rounded-0 border-0 border-bottom border-warning d-flex align-items-center gap-2 mb-0" style="margin-top:-1px">
        <i class="bi bi-eye-fill"></i>
        <span><strong>Admin Preview</strong> — You are viewing this quiz as an admin. Submissions are recorded against your account and do not affect student data.</span>
    </div>
    <?php endif; ?>

    <?php render_flash(); ?>

    <!-- Result card (after submission) -->
    <?php if ($result): ?>
    <div class="result-card mb-4">
        <div class="result-icon"><?= $result['passed'] ? '🎉' : '😔' ?></div>
        <div class="result-score <?= $result['passed'] ? 'pass' : 'fail' ?>"><?= $result['score'] ?>%</div>
        <h4 class="fw-700 mt-2"><?= $result['passed'] ? 'Congratulations! You passed!' : 'Not quite there yet.' ?></h4>
        <p class="text-muted">
            <?php if ($result['passed']): ?>
                You scored <?= $result['score'] ?>% and passed the <?= h($quiz['pass_mark']) ?>% pass mark.
                <?php $mc = get_module_completion($user['id'], $quiz['module_id']); if ($mc['complete']): ?>
                The <strong><?= h($module['title']) ?></strong> module is now complete!
                <?php endif; ?>
            <?php else: ?>
                You scored <?= $result['score'] ?>%. You need <?= $quiz['pass_mark'] ?>% to pass. Review the lessons and try again!
            <?php endif; ?>
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap mt-3">
            <?php if (!$result['passed']): ?>
            <button class="btn btn-primary" onclick="document.getElementById('retake-form').classList.remove('d-none');this.closest('.result-card').style.display='none'">
                <i class="bi bi-arrow-clockwise me-1"></i>Retake Quiz
            </button>
            <?php endif; ?>
            <a href="/pages/course.php?id=<?= $courseId ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Course
            </a>
            <?php
            // Find next lesson after this module
            $modules = get_modules_for_course($courseId);
            $foundModule = false;
            $nextLesson = null;
            foreach ($modules as $m) {
                if ($foundModule) {
                    $mLessons = get_lessons_for_module($m['id']);
                    if (!empty($mLessons)) { $nextLesson = $mLessons[0]; break; }
                }
                if ($m['id'] === $quiz['module_id']) $foundModule = true;
            }
            if ($nextLesson): ?>
            <a href="/pages/lesson.php?id=<?= $nextLesson['id'] ?>" class="btn btn-success">
                <i class="bi bi-arrow-right me-1"></i>Next Module
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Best attempt badge -->
    <?php if ($bestAttempt && !$result): ?>
    <div class="alert <?= $passed ? 'alert-success' : 'alert-warning' ?> d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-<?= $passed ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> fs-5"></i>
        <span>
            Your best score: <strong><?= $bestAttempt['score'] ?>%</strong>
            <?= $passed ? '— Passed ✓' : '— Needs ' . $quiz['pass_mark'] . '% to pass' ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- Quiz form -->
    <div id="retake-form" class="<?= ($result && !$result['passed']) ? 'd-none' : '' ?>">
        <?php if (!$result): ?>
        <div class="mb-4">
            <h5 class="fw-700">
                <?= $bestAttempt ? 'Retake Quiz' : 'Answer all questions below' ?>
            </h5>
            <p class="text-muted small">Choose one answer per question. You can retake the quiz as many times as needed.</p>
        </div>
        <?php endif; ?>

        <form id="quiz-form" action="/actions/submit_quiz.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="quiz_id" value="<?= $quizId ?>">

            <?php foreach ($questions as $qi => $question): ?>
            <div class="question-card" data-question="<?= $question['id'] ?>">
                <div class="question-number">Question <?= $qi + 1 ?> of <?= count($questions) ?></div>
                <div class="question-text"><?= h($question['question_text']) ?></div>

                <div class="options-list">
                    <?php foreach ($question['options'] as $option): ?>
                    <label class="option-label">
                        <input type="radio" name="answers[<?= $question['id'] ?>]" value="<?= $option['id'] ?>">
                        <span><?= h($option['text']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="d-flex gap-3 justify-content-between mt-4">
                <a href="/pages/course.php?id=<?= $courseId ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Course
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send me-1"></i>Submit Quiz
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
