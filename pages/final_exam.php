<?php
$pageTitle = 'Final Exam';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$courseId = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if (!$courseId) { header('Location: /pages/courses.php'); exit; }

$course = get_course($courseId);
if (!$course) { header('Location: /pages/courses.php'); exit; }

$exam = get_final_exam_for_course($courseId);
if (!$exam) {
    set_flash('info', 'No final exam is available for this course yet.');
    header('Location: /pages/course.php?id=' . $courseId);
    exit;
}

// Check if user has completed all course content first
if (!is_course_complete($user['id'], $courseId)) {
    set_flash('warning', 'You must complete all lessons and module quizzes before taking the final exam.');
    header('Location: /pages/course.php?id=' . $courseId);
    exit;
}

// Get best attempt
$bestAttempt = get_best_exam_attempt($user['id'], $exam['id']);

// Get questions
$questions = get_final_exam_questions($exam['id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/pages/courses.php">Courses</a></li>
                <li class="breadcrumb-item"><a href="/pages/course.php?id=<?= $courseId ?>"><?= h($course['title']) ?></a></li>
                <li class="breadcrumb-item active">Final Exam</li>
            </ol>
        </nav>
        <h1><?= h($exam['title']) ?></h1>
        <p class="mb-0"><?= h($exam['description'] ?? 'Demonstrate your mastery of ' . $course['title']) ?></p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php render_flash(); ?>

            <?php if ($bestAttempt && $bestAttempt['passed']): ?>
            <!-- Already passed -->
            <div class="result-card mb-4">
                <i class="bi bi-trophy-fill text-success result-icon"></i>
                <h3 class="mt-3 mb-0">Congratulations!</h3>
                <p class="text-muted mb-3">You've passed the <?= h($course['title']) ?> Final Exam</p>
                <div class="result-score pass"><?= $bestAttempt['score'] ?>%</div>
                <p class="mt-3">
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Passed on <?= date('M j, Y', strtotime($bestAttempt['completed_at'])) ?>
                    </span>
                </p>
                <div class="mt-4">
                    <a href="/pages/dashboard.php" class="btn btn-primary">
                        <i class="bi bi-house me-1"></i> Back to Dashboard
                    </a>
                    <a href="/pages/courses.php" class="btn btn-outline-secondary ms-2">
                        View Other Courses
                    </a>
                </div>
            </div>
            <?php elseif ($bestAttempt): ?>
            <!-- Previous attempt failed -->
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-repeat text-warning" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Previous Attempt: <?= $bestAttempt['score'] ?>%</h4>
                    <p class="text-muted">You need <?= $exam['pass_mark'] ?>% to pass. You can retake the exam.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Exam Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="fw-700 mb-4"><?= h($exam['title']) ?></h4>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="text-center p-3 rounded" style="background: var(--surface-hover);">
                                <i class="bi bi-clock text-primary mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-600"><?= $exam['time_limit'] ?> minutes</div>
                                <div class="small text-muted">Time Limit</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 rounded" style="background: var(--surface-hover);">
                                <i class="bi bi-list-check text-warning mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-600"><?= count($questions) ?> Questions</div>
                                <div class="small text-muted">MCQ + Coding</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 rounded" style="background: var(--surface-hover);">
                                <i class="bi bi-trophy text-success mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-600"><?= $exam['pass_mark'] ?>%</div>
                                <div class="small text-muted">Pass Mark</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-600 mb-3">Exam Guidelines:</h6>
                    <ul class="text-muted">
                        <li>This exam combines multiple-choice questions and coding exercises</li>
                        <li>You have <?= $exam['time_limit'] ?> minutes to complete all questions</li>
                        <li>You need at least <?= $exam['pass_mark'] ?>% to pass</li>
                        <li>Passing this exam contributes to your eligibility for HackathonAfrica</li>
                        <li>You can retake the exam if you don't pass</li>
                    </ul>

                    <div class="d-flex gap-3 mt-4">
                        <?php if (!$bestAttempt || !$bestAttempt['passed']): ?>
                        <a href="/pages/exam_take.php?id=<?= $exam['id'] ?>" class="btn btn-primary btn-lg" data-testid="start-exam-btn">
                            <i class="bi bi-play-fill me-1"></i>
                            <?= $bestAttempt ? 'Retake Exam' : 'Start Exam' ?>
                        </a>
                        <?php else: ?>
                        <a href="/pages/certificate.php?course_id=<?= $courseId ?>" class="btn btn-primary btn-lg" data-testid="view-certificate-btn">
                            <i class="bi bi-award me-1"></i> View Certificate
                        </a>
                        <?php endif; ?>
                        <a href="/pages/course.php?id=<?= $courseId ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i> Back to Course
                        </a>
                    </div>
                </div>
            </div>

            <!-- Exam Preparation Tips -->
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-700 mb-3">
                        <i class="bi bi-lightbulb text-warning me-2"></i>
                        Preparation Tips
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <span class="small">Review all lesson content thoroughly</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <span class="small">Practice the code exercises again</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <span class="small">Pay attention to common mistakes sections</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <span class="small">Make sure you understand, not just memorize</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
