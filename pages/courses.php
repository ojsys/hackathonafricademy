<?php
$pageTitle = 'Courses';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
$courses = get_all_published_courses();
require_once __DIR__ . '/../includes/header.php';

$icons = ['bi-filetype-html', 'bi-filetype-css', 'bi-filetype-js'];
$iconClasses = ['icon-html', 'icon-css', 'icon-js'];
?>

<div class="page-banner">
    <div class="container">
        <h1>Course Catalog</h1>
        <p>All training courses for HackathonAfrica qualification</p>
    </div>
</div>

<div class="container py-4">
    <?php render_flash(); ?>
    <div class="row g-4">
        <?php foreach ($courses as $i => $course):
            $idx = $i % 3;
            $enrolled = is_enrolled($user['id'], $course['id']);
            $locked   = is_course_locked($user['id'], $course['id']);
            $p = ($enrolled && !$locked) ? get_course_progress($user['id'], $course['id']) : null;
            $modules = get_modules_for_course($course['id']);
            $lessonCount = 0;
            foreach ($modules as $m) {
                $lessonCount += count(get_lessons_for_module($m['id']));
            }
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 course-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="course-icon <?= $iconClasses[$idx] ?>">
                            <i class="bi <?= $icons[$idx] ?>"></i>
                        </div>
                        <div>
                            <h5 class="fw-700 mb-1"><?= h($course['title']) ?></h5>
                            <span class="text-muted small">
                                <i class="bi bi-layers me-1"></i><?= count($modules) ?> modules
                                &middot; <i class="bi bi-file-text me-1"></i><?= $lessonCount ?> lessons
                            </span>
                        </div>
                    </div>
                    <p class="text-muted small mb-3"><?= h($course['description']) ?></p>

                    <?php if ($enrolled && $p): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Progress</span>
                            <span><?= $p['percent'] ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" data-width="<?= $p['percent'] ?>" style="width:0"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Module list preview -->
                    <div class="mt-2">
                        <?php foreach ($modules as $m): ?>
                        <div class="d-flex align-items-center gap-2 small text-muted mb-1">
                            <?php if ($enrolled):
                                $mc = get_module_completion($user['id'], $m['id']); ?>
                            <i class="bi bi-<?= $mc['complete'] ? 'check-circle-fill text-success' : 'circle' ?>" style="font-size:0.75rem"></i>
                            <?php else: ?>
                            <i class="bi bi-circle" style="font-size:0.75rem"></i>
                            <?php endif; ?>
                            <?= h($m['title']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent px-4 pb-4 pt-0">
                    <?php if ($locked): ?>
                    <button class="btn btn-secondary btn-sm w-100" disabled>
                        <i class="bi bi-lock-fill me-1"></i>Complete previous course first
                    </button>
                    <?php elseif ($enrolled): ?>
                    <?php $next = get_next_lesson($user['id'], $course['id']); ?>
                    <?php if ($next): ?>
                    <a href="/pages/lesson.php?id=<?= $next['id'] ?>" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-play-fill me-1"></i>
                        <?= $p && $p['done'] > 0 ? 'Continue Learning' : 'Start Course' ?>
                    </a>
                    <?php else: ?>
                    <a href="/pages/course.php?id=<?= $course['id'] ?>" class="btn btn-outline-success btn-sm w-100">
                        <i class="bi bi-trophy me-1"></i>Course Complete — Review
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <form action="/actions/enroll.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-plus-circle me-1"></i>Enroll Now — Free
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
