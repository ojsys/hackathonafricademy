<?php
$pageTitle = 'Exam Results';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$result = $_SESSION['exam_result'] ?? null;
if (!$result) {
    header('Location: /pages/courses.php');
    exit;
}
unset($_SESSION['exam_result']);

$courseId = $result['course_id'];
$course = get_course($courseId);
$passed = $result['passed'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card text-center" data-testid="exam-result-card">
                <div class="card-body py-5 px-4">
                    <?php if ($passed): ?>
                    <div style="font-size: 4rem; color: var(--success);">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <h2 class="fw-800 mt-3 mb-1">Congratulations!</h2>
                    <p class="text-muted mb-4">You passed the <?= h($course['title']) ?> Final Exam</p>
                    <?php else: ?>
                    <div style="font-size: 4rem; color: var(--warning);">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h2 class="fw-800 mt-3 mb-1">Keep Going!</h2>
                    <p class="text-muted mb-4">You need <?= $result['pass_mark'] ?>% to pass. Review and try again!</p>
                    <?php endif; ?>

                    <div class="result-score <?= $passed ? 'pass' : 'fail' ?>" data-testid="exam-score">
                        <?= $result['score'] ?>%
                    </div>

                    <div class="row g-3 mt-4 mb-4">
                        <div class="col-4">
                            <div class="p-3 rounded" style="background: var(--surface-hover);">
                                <div class="fw-700 h5 mb-0"><?= $result['mcq_score'] ?>%</div>
                                <div class="small text-muted">MCQ Score</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded" style="background: var(--surface-hover);">
                                <div class="fw-700 h5 mb-0"><?= $result['coding_score'] ?>%</div>
                                <div class="small text-muted">Coding Score</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded" style="background: var(--surface-hover);">
                                <div class="fw-700 h5 mb-0"><?= $result['total_questions'] ?></div>
                                <div class="small text-muted">Questions</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($result['time_taken'] > 0): ?>
                    <p class="small text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Completed in <?= floor($result['time_taken'] / 60) ?>m <?= $result['time_taken'] % 60 ?>s
                    </p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <?php if ($passed): ?>
                        <a href="/pages/certificate.php?course_id=<?= $courseId ?>" class="btn btn-primary" data-testid="view-certificate">
                            <i class="bi bi-award me-1"></i> View Certificate
                        </a>
                        <?php else: ?>
                        <a href="/pages/final_exam.php?course_id=<?= $courseId ?>" class="btn btn-primary" data-testid="retake-exam">
                            <i class="bi bi-arrow-repeat me-1"></i> Retake Exam
                        </a>
                        <?php endif; ?>
                        <a href="/pages/dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-1"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
