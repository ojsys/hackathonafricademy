<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$totalStudents   = count_users();
$totalEnrollments = count_enrollments();
$totalAttempts   = count_quiz_attempts();
$courseStats     = get_course_stats();

// Recent users
$recentUsers = db()->query('SELECT name, email, created_at FROM users WHERE role = "student" ORDER BY created_at DESC LIMIT 8')->fetchAll();

// Pass rates
$passRate = db()->query('SELECT ROUND(AVG(passed) * 100) AS rate FROM quiz_attempts')->fetchColumn();

// Eligible candidates
$eligibleCount = db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "eligible"')->fetchColumn() ?: 0;
$pendingReview = db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "needs_review"')->fetchColumn() ?: 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <h1 class="admin-page-title">Admin Dashboard</h1>

        <?php render_flash(); ?>

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="stat-value"><?= $totalStudents ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-book-half"></i></div>
                    <div>
                        <div class="stat-value"><?= $totalEnrollments ?></div>
                        <div class="stat-label">Enrollments</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <div class="stat-value"><?= $totalAttempts ?></div>
                        <div class="stat-label">Quiz Attempts</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <div class="stat-value"><?= $passRate ?? 0 ?>%</div>
                        <div class="stat-label">Quiz Pass Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Course enrollment stats -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">Course Enrollments</h5>
                        <?php foreach ($courseStats as $cs): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-500 mb-1">
                                <span><?= h($cs['title']) ?></span>
                                <span><?= $cs['enrolled'] ?> students</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" data-width="<?= $totalStudents > 0 ? round($cs['enrolled'] / $totalStudents * 100) : 0 ?>" style="width:0"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent signups -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-700 mb-0">Recent Students</h5>
                            <a href="/admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $u): ?>
                                    <tr>
                                        <td class="fw-500"><?= h($u['name']) ?></td>
                                        <td class="text-muted small"><?= h($u['email']) ?></td>
                                        <td class="text-muted small"><?= date('M j', strtotime($u['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentUsers)): ?>
                                    <tr><td colspan="3" class="text-muted text-center py-3">No students yet</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Candidate Overview -->
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="card" style="border-left: 3px solid var(--primary);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">Eligible Candidates</h6>
                                <div class="h2 mb-0 text-success"><?= $eligibleCount ?></div>
                                <p class="small text-muted mb-0">Ready for HackathonAfrica</p>
                            </div>
                            <div class="stat-icon green">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                        </div>
                        <a href="/admin/candidates.php?status=eligible" class="btn btn-sm btn-outline-primary mt-3">
                            View Eligible <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card" style="border-left: 3px solid var(--warning);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-1">Needs Review</h6>
                                <div class="h2 mb-0 text-warning"><?= $pendingReview ?></div>
                                <p class="small text-muted mb-0">Awaiting admin review</p>
                            </div>
                            <div class="stat-icon orange">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                        </div>
                        <a href="/admin/candidates.php?status=needs_review" class="btn btn-sm btn-outline-warning mt-3">
                            Review Now <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="fw-700 mb-3">Quick Actions</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/admin/candidates.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-check me-1"></i>Review Candidates
                    </a>
                    <a href="/admin/analytics.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-graph-up me-1"></i>View Analytics
                    </a>
                    <a href="/admin/courses.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-collection me-1"></i>Manage Courses
                    </a>
                    <a href="/admin/users.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i>Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
