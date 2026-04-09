<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$courseId) { header('Location: /pages/courses.php'); exit; }

$course = get_course($courseId);
if (!$course || $course['status'] !== 'published') { header('Location: /pages/courses.php'); exit; }

// Auto-enroll if not enrolled
if (!is_enrolled($user['id'], $courseId)) {
    enroll_user($user['id'], $courseId);
}

$pageTitle = $course['title'];
$modules = get_modules_for_course($courseId);
$progress = get_course_progress($user['id'], $courseId);
$complete = is_course_complete($user['id'], $courseId);
$next = get_next_lesson($user['id'], $courseId);

// Find the actual next accessible lesson (respects quiz gate)
$nextAccessible = null;
foreach ($modules as $m) {
    if (!is_module_accessible($user['id'], $m['id'])) break;
    $mLessons = get_lessons_for_module($m['id']);
    foreach ($mLessons as $l) {
        if (!is_lesson_completed($user['id'], $l['id'])) {
            $nextAccessible = $l;
            break 2;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
$icons = ['bi-filetype-html', 'bi-filetype-css', 'bi-filetype-js'];
$iconClasses = ['icon-html', 'icon-css', 'icon-js'];
$idx = ($courseId - 1) % 3;
?>

<div class="page-banner">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/pages/courses.php" style="color:rgba(255,255,255,0.7)">Courses</a></li>
                <li class="breadcrumb-item active text-white"><?= h($course['title']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="course-icon <?= $iconClasses[$idx] ?>">
                <i class="bi <?= $icons[$idx] ?>"></i>
            </div>
            <div>
                <h1 class="mb-0"><?= h($course['title']) ?></h1>
                <p><?= h($course['description']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <?php render_flash(); ?>

    <!-- Progress summary -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span class="fw-600">Overall Progress</span>
                        <span><?= $progress['done'] ?>/<?= $progress['total'] ?> lessons &middot; <?= $progress['percent'] ?>%</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar" data-width="<?= $progress['percent'] ?>" style="width:0"></div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if ($nextAccessible): ?>
                    <a href="/pages/lesson.php?id=<?= $nextAccessible['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-play-fill me-1"></i>
                        <?= $progress['done'] > 0 ? 'Continue Learning' : 'Start Course' ?>
                    </a>
                    <?php elseif ($complete): ?>
                    <span class="badge bg-success fs-6 p-2"><i class="bi bi-trophy-fill me-1"></i>Course Complete!</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz gate notice -->
    <div class="alert alert-info d-flex gap-2 align-items-start mb-4 py-2">
        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
        <span class="small">Each module is <strong>unlocked only after passing the previous module's quiz</strong>. You must score <?= 70 ?>% or above to proceed.</span>
    </div>

    <!-- Modules -->
    <h5 class="fw-700 mb-3">Course Content &mdash; <?= count($modules) ?> Modules</h5>
    <div class="accordion" id="modulesAccordion">
        <?php foreach ($modules as $mi => $module):
            $lessons = get_lessons_for_module($module['id']);
            $quiz = get_quiz_for_module($module['id']);
            $mc = get_module_completion($user['id'], $module['id']);
            $accessible = is_module_accessible($user['id'], $module['id']);
        ?>
        <div class="accordion-item border mb-2 rounded overflow-hidden <?= !$accessible ? 'opacity-75' : '' ?>">
            <h2 class="accordion-header">
                <button class="accordion-button <?= ($mi > 0 && !$mc['lessons_done']) ? 'collapsed' : '' ?> fw-600
                    <?= !$accessible ? 'bg-light text-muted' : '' ?>" type="button"
                        data-bs-toggle="collapse" data-bs-target="#module<?= $module['id'] ?>">
                    <span class="me-2">
                        <?php if (!$accessible): ?>
                        <i class="bi bi-lock-fill text-muted"></i>
                        <?php elseif ($mc['complete']): ?>
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <?php elseif ($mc['lessons_done'] > 0): ?>
                        <i class="bi bi-circle-half text-warning"></i>
                        <?php else: ?>
                        <i class="bi bi-circle text-muted"></i>
                        <?php endif; ?>
                    </span>
                    <?= h($module['title']) ?>
                    <?php if (!$accessible): ?>
                    <span class="ms-2 badge bg-secondary fw-400" style="font-size:0.7rem">Locked</span>
                    <?php endif; ?>
                    <span class="ms-auto me-2 small text-muted fw-400">
                        <?= count($lessons) ?> lessons
                        <?= $quiz ? ($mc['quiz_passed'] ? '&middot; Quiz ✓' : '&middot; Quiz required') : '' ?>
                    </span>
                </button>
            </h2>
            <div id="module<?= $module['id'] ?>" class="accordion-collapse collapse <?= ($mi === 0 || $mc['lessons_done'] > 0) ? 'show' : '' ?>">
                <div class="accordion-body p-0">
                    <?php if (!$accessible): ?>
                    <div class="d-flex align-items-center gap-3 px-4 py-3 text-muted bg-light">
                        <i class="bi bi-lock-fill"></i>
                        <span class="small">Pass the previous module's quiz to unlock this section.</span>
                    </div>
                    <?php else: ?>

                    <?php foreach ($lessons as $lesson):
                        $done = is_lesson_completed($user['id'], $lesson['id']);
                    ?>
                    <a href="/pages/lesson.php?id=<?= $lesson['id'] ?>"
                       class="d-flex align-items-center gap-3 px-4 py-2 text-decoration-none border-bottom <?= $done ? 'bg-success bg-opacity-10' : '' ?>"
                       style="color:inherit">
                        <i class="bi bi-<?= $done ? 'check-circle-fill text-success' : 'play-circle text-primary' ?>"></i>
                        <span class="flex-grow-1 small <?= $done ? 'fw-500' : '' ?>"><?= h($lesson['title']) ?></span>
                        <?php if ($done): ?>
                        <span class="badge bg-success-subtle text-success small">Done</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>

                    <?php if ($quiz): ?>
                    <?php $attempt = get_best_quiz_attempt($user['id'], $quiz['id']); ?>
                    <a href="/pages/quiz.php?id=<?= $quiz['id'] ?>"
                       class="d-flex align-items-center gap-3 px-4 py-3 text-decoration-none border-top <?= $mc['quiz_passed'] ? 'bg-warning bg-opacity-10' : 'bg-primary bg-opacity-5' ?>"
                       style="color:inherit">
                        <i class="bi bi-<?= $mc['quiz_passed'] ? 'check-circle-fill text-success' : 'question-circle-fill text-warning' ?> fs-5"></i>
                        <div class="flex-grow-1">
                            <div class="fw-600 small">Module Quiz: <?= h($quiz['title']) ?></div>
                            <div class="text-muted" style="font-size:0.75rem">
                                <?= count(get_questions_for_quiz($quiz['id'])) ?> questions &middot; Pass mark: <?= $quiz['pass_mark'] ?>%
                                <?= $mc['quiz_passed'] ? '&middot; <span class="text-success">Passed ✓</span>' : '&middot; <strong>Required to unlock next module</strong>' ?>
                            </div>
                        </div>
                        <?php if ($attempt): ?>
                        <span class="badge <?= $attempt['passed'] ? 'bg-success' : 'bg-danger' ?> fs-6"><?= $attempt['score'] ?>%</span>
                        <?php else: ?>
                        <span class="badge bg-primary">Start Quiz</span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php 
    // Show Final Exam section if course is complete or nearly complete
    $exam = get_final_exam_for_course($courseId);
    if ($exam): 
        $examAttempt = get_best_exam_attempt($user['id'], $exam['id']);
    ?>
    <!-- Final Exam Section -->
    <div class="card mt-4" style="border-left: 3px solid <?= $complete ? 'var(--primary)' : 'var(--border)' ?>;">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <div class="stat-icon <?= $complete ? 'green' : '' ?>" style="<?= !$complete ? 'background: var(--surface-hover); color: var(--text-muted);' : '' ?>">
                    <i class="bi bi-award"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-700 mb-1"><?= h($exam['title']) ?></h5>
                    <p class="text-muted small mb-3">
                        <?= h($exam['description'] ?? 'Comprehensive assessment combining MCQs and coding exercises. Pass this exam to prove your ' . $course['title'] . ' skills.') ?>
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3 mb-3 small">
                        <span><i class="bi bi-clock me-1"></i> <?= $exam['time_limit'] ?> minutes</span>
                        <span><i class="bi bi-trophy me-1"></i> <?= $exam['pass_mark'] ?>% to pass</span>
                        <?php if ($examAttempt): ?>
                        <span class="badge <?= $examAttempt['passed'] ? 'bg-success' : 'bg-danger' ?>">
                            Best Score: <?= $examAttempt['score'] ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$complete): ?>
                    <div class="alert alert-warning small py-2 mb-3">
                        <i class="bi bi-lock me-1"></i> Complete all lessons and module quizzes to unlock the final exam.
                    </div>
                    <?php endif; ?>
                    
                    <a href="/pages/final_exam.php?course_id=<?= $courseId ?>" 
                       class="btn <?= $complete ? ($examAttempt && $examAttempt['passed'] ? 'btn-success' : 'btn-primary') : 'btn-secondary' ?>"
                       <?= !$complete ? 'disabled' : '' ?>>
                        <?php if ($examAttempt && $examAttempt['passed']): ?>
                            <i class="bi bi-check-circle me-1"></i> Exam Passed
                        <?php elseif ($examAttempt): ?>
                            <i class="bi bi-arrow-repeat me-1"></i> Retake Exam
                        <?php else: ?>
                            <i class="bi bi-play-fill me-1"></i> Take Final Exam
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
