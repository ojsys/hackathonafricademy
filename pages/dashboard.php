<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
$courses = get_all_published_courses();
$eligible = is_eligible($user['id']);

// Stats
$totalLessons = 0;
$doneLessons  = 0;
$enrolledCount = 0;
$progressData = [];

foreach ($courses as $c) {
    if (!is_enrolled($user['id'], $c['id'])) continue;
    $enrolledCount++;
    $p = get_course_progress($user['id'], $c['id']);
    $progressData[$c['id']] = $p;
    $totalLessons += $p['total'];
    $doneLessons  += $p['done'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page banner -->
<div class="page-banner">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="mb-1">Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, <?= h(explode(' ', $user['name'])[0]) ?>! 👋</h1>
                <p>Continue where you left off and keep building your skills.</p>
            </div>
            <?php if ($eligible): ?>
            <div class="eligibility-badge eligible">
                <i class="bi bi-check-circle-fill"></i> Eligible for HackathonAfrica
            </div>
            <?php else: ?>
            <div class="eligibility-badge ineligible">
                <i class="bi bi-hourglass-split"></i> Complete all courses to qualify
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-4">

    <?php render_flash(); ?>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-book"></i></div>
                <div>
                    <div class="stat-value"><?= $enrolledCount ?></div>
                    <div class="stat-label">Courses Enrolled</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check2-square"></i></div>
                <div>
                    <div class="stat-value"><?= $doneLessons ?></div>
                    <div class="stat-label">Lessons Completed</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-bar-chart"></i></div>
                <div>
                    <div class="stat-value"><?= $totalLessons > 0 ? round($doneLessons / $totalLessons * 100) : 0 ?>%</div>
                    <div class="stat-label">Overall Progress</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon <?= $eligible ? 'green' : 'red' ?>">
                    <i class="bi bi-<?= $eligible ? 'trophy-fill' : 'lock' ?>"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.1rem"><?= $eligible ? 'Eligible' : 'Locked' ?></div>
                    <div class="stat-label">Hackathon Status</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My courses -->
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-700 mb-0">My Courses</h5>
                <a href="/pages/courses.php" class="btn btn-sm btn-outline-primary">Browse All</a>
            </div>

            <?php
            $icons = ['bi-filetype-html', 'bi-filetype-css', 'bi-filetype-js'];
            $iconClasses = ['icon-html', 'icon-css', 'icon-js'];
            $i = 0;
            $hasEnrolled = false;
            foreach ($courses as $course):
                if (!is_enrolled($user['id'], $course['id'])) { $i++; continue; }
                $hasEnrolled = true;
                $p = $progressData[$course['id']] ?? ['percent' => 0, 'done' => 0, 'total' => 0];
                $complete = is_course_complete($user['id'], $course['id']);
                $next = get_next_lesson($user['id'], $course['id']);
                $idx = $i % 3;
            ?>
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="course-icon <?= $iconClasses[$idx] ?>">
                            <i class="bi <?= $icons[$idx] ?>"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <h6 class="fw-700 mb-0"><?= h($course['title']) ?></h6>
                                <?php if ($complete): ?>
                                <span class="badge bg-success rounded-pill"><i class="bi bi-check2"></i> Completed</span>
                                <?php endif; ?>
                            </div>
                            <div class="my-2">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><?= $p['done'] ?>/<?= $p['total'] ?> lessons</span>
                                    <span><?= $p['percent'] ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" data-width="<?= $p['percent'] ?>" style="width:0"></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <?php if ($next): ?>
                                <a href="/pages/lesson.php?id=<?= $next['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-play-fill me-1"></i>
                                    <?= $p['done'] > 0 ? 'Continue' : 'Start' ?>
                                </a>
                                <?php else: ?>
                                <a href="/pages/course.php?id=<?= $course['id'] ?>" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-trophy me-1"></i>Review Course
                                </a>
                                <?php endif; ?>
                                <a href="/pages/course.php?id=<?= $course['id'] ?>" class="btn btn-outline-secondary btn-sm">Overview</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $i++; endforeach; ?>

            <?php if (!$hasEnrolled): ?>
            <div class="empty-state card">
                <div class="card-body">
                    <i class="bi bi-book text-muted"></i>
                    <h6 class="fw-600">No courses yet</h6>
                    <p class="text-muted">Browse our catalog and enroll in your first course.</p>
                    <a href="/pages/courses.php" class="btn btn-primary btn-sm">Browse Courses</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar: quick links & eligibility -->
        <div class="col-lg-4">
            <!-- Eligibility tracker -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="fw-700 mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Hackathon Eligibility</h6>
                    <?php foreach ($courses as $j => $c): ?>
                    <?php $complete = is_course_complete($user['id'], $c['id']); ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-<?= $complete ? 'check-circle-fill text-success' : 'circle text-muted' ?> fs-5"></i>
                        <span class="small <?= $complete ? 'fw-600' : 'text-muted' ?>"><?= h($c['title']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($eligible): ?>
                    <div class="alert alert-success small mt-3 mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <strong>Congratulations!</strong> You are eligible for HackathonAfrica.
                    </div>
                    <?php else: ?>
                    <p class="text-muted small mt-3 mb-0">Complete all courses and pass all quizzes to unlock your eligibility badge.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick links -->
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-700 mb-3">Quick Links</h6>
                    <div class="d-grid gap-2">
                        <a href="/pages/courses.php" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-collection me-2"></i>All Courses
                        </a>
                        <a href="/pages/profile.php" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="bi bi-person me-2"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
