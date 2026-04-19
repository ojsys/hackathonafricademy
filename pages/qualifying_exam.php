<?php
$pageTitle = 'Final Exam';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$exam = get_qualifying_exam();
$eligible = is_eligible($user['id']);
$bestAttempt = get_best_qualifying_attempt($user['id']);
$activeAttempt = get_active_qualifying_attempt($user['id']);
$passed = has_passed_qualifying_exam($user['id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 class="mb-1"><i class="bi bi-shield-check me-2" style="color:var(--primary)"></i>Final Exam</h1>
        <p class="mb-0">The proctored final exam that evaluates your overall understanding across all courses.</p>
    </div>
</div>

<div class="container py-5">
    <?php render_flash(); ?>

    <?php if (!$exam): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>The final exam has not been set up yet. Please check back soon.</div>

    <?php elseif ($passed): ?>
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card p-5">
                <i class="bi bi-trophy-fill mb-3" style="font-size:4rem;color:var(--primary)"></i>
                <h2 class="fw-700 mb-2">You Passed!</h2>
                <p class="text-muted mb-1">Score: <strong class="text-success"><?= $bestAttempt['percentage'] ?>%</strong></p>
                <p class="text-muted">Completed on <?= date('M j, Y', strtotime($bestAttempt['completed_at'])) ?></p>
                <a href="/pages/dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <?php elseif (!$eligible): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4">
                <h4 class="fw-700 mb-3"><i class="bi bi-lock me-2 text-warning"></i>Complete All Courses First</h4>
                <p class="text-muted mb-4">You must finish all modules and pass all quizzes across every published course before attempting the final exam.</p>
                <?php foreach (get_all_published_courses() as $c): ?>
                <?php $complete = is_course_complete($user['id'], $c['id']); ?>
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background:var(--surface-hover)">
                    <i class="bi bi-<?= $complete ? 'check-circle-fill text-success' : 'circle text-muted' ?>" style="font-size:1.25rem"></i>
                    <div class="flex-grow-1">
                        <div class="fw-600"><?= h($c['title']) ?></div>
                    </div>
                    <?php if (!$complete): ?>
                    <a href="/pages/course.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Continue</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="row justify-content-center g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="fw-700 mb-1"><?= h($exam['title']) ?></h3>
                    <?php if ($exam['description']): ?>
                    <p class="text-muted mb-4"><?= h($exam['description']) ?></p>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-4 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= $exam['time_limit'] ?></div>
                            <div class="small text-muted">Minutes</div>
                        </div>
                        <div class="col-4 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)">
                                <?php
                                $qCount = db()->prepare('SELECT COUNT(*) FROM qualifying_questions WHERE exam_id = ?');
                                $qCount->execute([$exam['id']]);
                                echo $qCount->fetchColumn();
                                ?>
                            </div>
                            <div class="small text-muted">Questions</div>
                        </div>
                        <div class="col-4 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= $exam['pass_mark'] ?>%</div>
                            <div class="small text-muted">Pass Mark</div>
                        </div>
                    </div>

                    <?php if ($exam['instructions']): ?>
                    <div class="alert alert-warning mb-4">
                        <strong><i class="bi bi-info-circle me-1"></i>Instructions:</strong><br>
                        <?= nl2br(h($exam['instructions'])) ?>
                    </div>
                    <?php endif; ?>

                    <div class="alert mb-4" style="background:rgba(248,181,38,0.1);border:1px solid rgba(248,181,38,0.3)">
                        <strong><i class="bi bi-camera-video me-1" style="color:var(--primary)"></i>This exam is proctored.</strong>
                        Your device camera will be used to take periodic snapshots during the exam. Ensure you are in a well-lit, quiet space.
                    </div>

                    <?php if ($bestAttempt): ?>
                    <div class="p-3 rounded mb-4" style="background:var(--surface-hover)">
                        <div class="small text-muted mb-1">Your best previous attempt</div>
                        <span class="fw-700 <?= $bestAttempt['passed'] ? 'text-success' : 'text-danger' ?>"><?= $bestAttempt['percentage'] ?>%</span>
                        <span class="text-muted small ms-2"><?= date('M j, Y', strtotime($bestAttempt['completed_at'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($activeAttempt): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-1"></i>You have an unfinished attempt. Resuming it now.
                    </div>
                    <a href="/pages/qualifying_take.php" class="btn btn-warning btn-lg w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Resume Exam
                    </a>
                    <?php else: ?>
                    <form method="POST" action="/actions/start_qualifying_exam.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-lg w-100"
                            onclick="return confirm('This exam is proctored — your camera will be activated. Ready to start your Final Exam?')">
                            <i class="bi bi-play-circle me-2"></i><?= $bestAttempt ? 'Retake Exam' : 'Start Exam' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3"><i class="bi bi-lightbulb me-1 text-warning"></i>Tips</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">Ensure your device camera is working.</li>
                        <li class="mb-2">Use a stable internet connection.</li>
                        <li class="mb-2">Sit in a well-lit, quiet environment.</li>
                        <li class="mb-2">Do not leave the exam tab or cover the camera.</li>
                        <li>All questions must be answered before submitting.</li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3"><i class="bi bi-collection me-1" style="color:var(--primary)"></i>Coverage</h6>
                    <?php foreach (get_all_published_courses() as $c): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success small"></i>
                        <span class="small"><?= h($c['title']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
