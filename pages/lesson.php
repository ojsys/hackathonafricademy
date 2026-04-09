<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$lessonId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$lessonId) { header('Location: /pages/courses.php'); exit; }

$lesson = get_lesson($lessonId);
if (!$lesson) { header('Location: /pages/courses.php'); exit; }

$moduleId = $lesson['module_id'];
$courseId = $lesson['course_id'];

// Auto-enroll
if (!is_enrolled($user['id'], $courseId)) {
    enroll_user($user['id'], $courseId);
}

// ── Quiz gate: block access if a previous module quiz hasn't been passed ──
if (!is_module_accessible($user['id'], $moduleId)) {
    set_flash('error', 'You must pass the quiz for the previous module before accessing this one.');
    header('Location: /pages/course.php?id=' . $courseId);
    exit;
}

$course  = get_course($courseId);
$modules = get_modules_for_course($courseId);
$completed = is_lesson_completed($user['id'], $lessonId);

// Get the module record (for video_url)
$stmt = db()->prepare('SELECT * FROM modules WHERE id = ?');
$stmt->execute([$moduleId]);
$currentModule = $stmt->fetch();

// Find prev/next lesson — only within accessible modules
$allLessons = [];
foreach ($modules as $m) {
    if (!is_module_accessible($user['id'], $m['id'])) break;
    foreach (get_lessons_for_module($m['id']) as $l) {
        $allLessons[] = $l;
    }
}
$currentIdx = array_search($lessonId, array_column($allLessons, 'id'));
$prevLesson = $currentIdx > 0 ? $allLessons[$currentIdx - 1] : null;
$nextLesson = isset($allLessons[$currentIdx + 1]) ? $allLessons[$currentIdx + 1] : null;

// Quiz for this module (shown after last lesson in module)
$quiz = get_quiz_for_module($moduleId);
$moduleLessons = get_lessons_for_module($moduleId);
$isLastInModule = end($moduleLessons)['id'] === $lessonId;
$quizPassed = false;
if ($quiz) {
    $attempt = get_best_quiz_attempt($user['id'], $quiz['id']);
    $quizPassed = $attempt && $attempt['passed'];
}

$pageTitle = $lesson['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="lesson-layout">
    <!-- Sidebar -->
    <aside class="lesson-sidebar d-none d-lg-block">
        <div class="sidebar-header">
            <a href="/pages/course.php?id=<?= $courseId ?>" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i> <?= h($course['title']) ?>
            </a>
        </div>

        <?php foreach ($modules as $mod):
            $modLessons = get_lessons_for_module($mod['id']);
            $modQuiz = get_quiz_for_module($mod['id']);
            $mc = get_module_completion($user['id'], $mod['id']);
            $accessible = is_module_accessible($user['id'], $mod['id']);
        ?>
        <div class="sidebar-module <?= !$accessible ? 'sidebar-module-locked' : '' ?>">
            <div class="sidebar-module-title">
                <span>
                    <?php if (!$accessible): ?>
                    <i class="bi bi-lock-fill text-muted me-1" style="font-size:0.75rem"></i>
                    <?php endif; ?>
                    <?= h($mod['title']) ?>
                </span>
                <span class="module-status <?= $mc['complete'] ? 'done' : ($mc['lessons_done'] > 0 ? 'wip' : 'todo') ?>">
                    <i class="bi bi-<?= $mc['complete'] ? 'check-circle-fill' : ($accessible ? ($mc['lessons_done'] > 0 ? 'circle-half' : 'circle') : 'lock-fill') ?>"></i>
                </span>
            </div>

            <?php if ($accessible): ?>
            <?php foreach ($modLessons as $l):
                $lDone = is_lesson_completed($user['id'], $l['id']);
                $isActive = $l['id'] === $lessonId;
            ?>
            <a href="/pages/lesson.php?id=<?= $l['id'] ?>"
               class="sidebar-lesson-link <?= $isActive ? 'active' : '' ?> <?= $lDone ? 'done' : '' ?>">
                <i class="bi bi-<?= $lDone ? 'check-circle-fill text-success' : ($isActive ? 'play-circle-fill text-primary' : 'circle') ?> lesson-check"></i>
                <?= h($l['title']) ?>
            </a>
            <?php endforeach; ?>

            <?php if ($modQuiz):
                $attempt = get_best_quiz_attempt($user['id'], $modQuiz['id']); ?>
            <a href="/pages/quiz.php?id=<?= $modQuiz['id'] ?>" class="sidebar-quiz-link <?= ($attempt && $attempt['passed']) ? 'quiz-passed' : '' ?>">
                <i class="bi bi-<?= ($attempt && $attempt['passed']) ? 'check-circle-fill text-success' : 'question-circle-fill text-warning' ?>"></i>
                Quiz: <?= h($modQuiz['title']) ?>
                <?php if ($attempt): ?><span class="ms-auto badge <?= $attempt['passed'] ? 'bg-success' : 'bg-danger' ?>"><?= $attempt['score'] ?>%</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php else: ?>
            <div class="sidebar-lesson-link text-muted" style="opacity:0.5;pointer-events:none">
                <i class="bi bi-lock me-1"></i><em>Complete previous quiz to unlock</em>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </aside>

    <!-- Main content -->
    <div>
        <div class="lesson-content">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/pages/courses.php">Courses</a></li>
                    <li class="breadcrumb-item"><a href="/pages/course.php?id=<?= $courseId ?>"><?= h($course['title']) ?></a></li>
                    <li class="breadcrumb-item active"><?= h($lesson['title']) ?></li>
                </ol>
            </nav>

            <?php render_flash(); ?>

            <!-- Completion banner -->
            <?php if ($completed): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>You completed this lesson!
                    <?php if ($isLastInModule && $quiz && !$quizPassed): ?>
                        Now take the module quiz to unlock the next section.
                    <?php elseif ($nextLesson): ?>
                        Continue to the next one.
                    <?php else: ?>
                        You've reached the end of this course section!
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Module intro video (if set) -->
            <?php if (!empty($currentModule['video_url'])): ?>
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-play-btn-fill text-primary fs-5"></i>
                    <h6 class="fw-700 mb-0">Module Overview Video</h6>
                </div>
                <div class="ratio ratio-16x9 rounded overflow-hidden border">
                    <iframe src="<?= h($currentModule['video_url']) ?>" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lesson content (rendered HTML from database) -->
            <div class="lesson-body">
                <?= $lesson['content'] /* Content is admin-authored HTML; sanitize on input */ ?>
            </div>

            <?php if ($lesson['video_url']): ?>
            <div class="mt-4">
                <h6 class="fw-700 mb-2"><i class="bi bi-play-btn me-2"></i>Lesson Video</h6>
                <div class="ratio ratio-16x9 rounded overflow-hidden border">
                    <iframe src="<?= h($lesson['video_url']) ?>" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
            </div>
            <?php elseif ($lesson['video_placeholder']): ?>
            <div class="video-placeholder">
                <i class="bi bi-play-btn"></i>
                <p>Video content coming soon</p>
            </div>
            <?php endif; ?>

            <?php 
            // Render code exercises if any exist for this lesson
            require_once __DIR__ . '/../includes/code_exercise.php';
            render_lesson_exercises($lessonId, $user['id']);
            
            // Load Monaco Editor if exercises exist
            $exercises = get_exercises_for_lesson($lessonId);
            if (!empty($exercises)) {
                render_monaco_loader();
            }
            ?>
        </div>

        <!-- Nav bar -->
        <div class="lesson-nav-bar">
            <div>
                <?php if ($prevLesson): ?>
                <a href="/pages/lesson.php?id=<?= $prevLesson['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Previous
                </a>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <?php if ($isLastInModule && $quiz && !$quizPassed): ?>
                <!-- Must pass quiz before proceeding to next module -->
                <?php if (!$completed): ?>
                <form action="/actions/complete_lesson.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                    <input type="hidden" name="redirect" value="/pages/lesson.php?id=<?= $lessonId ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2 me-1"></i>Mark as Complete
                    </button>
                </form>
                <?php endif; ?>
                <a href="/pages/quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-warning fw-600">
                    <i class="bi bi-question-circle me-1"></i>Take Module Quiz to Proceed
                </a>

                <?php elseif (!$completed): ?>
                <form action="/actions/complete_lesson.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                    <input type="hidden" name="redirect" value="/pages/lesson.php?id=<?= $lessonId ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2 me-1"></i>Mark as Complete
                    </button>
                </form>

                <?php else: ?>
                <!-- Lesson completed, quiz either passed or not required -->
                <?php if ($nextLesson): ?>
                <a href="/pages/lesson.php?id=<?= $nextLesson['id'] ?>" class="btn btn-primary">
                    Next Lesson <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <?php else: ?>
                <a href="/pages/course.php?id=<?= $courseId ?>" class="btn btn-primary">
                    <i class="bi bi-house me-1"></i>Course Overview
                </a>
                <?php endif; ?>
                <?php endif; ?>

                <?php if ($completed && $nextLesson && !($isLastInModule && $quiz && !$quizPassed)): ?>
                <!-- already shown above via next lesson link -->
                <?php elseif ($completed && !$isLastInModule): ?>
                <?php if ($nextLesson): ?>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
