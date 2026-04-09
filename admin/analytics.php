<?php
$pageTitle = 'Analytics Dashboard';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$analytics = get_analytics_overview();
$courseAnalytics = get_course_analytics();

// Get recent activity
$recentActivity = db()->query('
    SELECT u.name, u.email, 
           CASE 
               WHEN qa.id IS NOT NULL THEN "Completed quiz"
               WHEN ulp.id IS NOT NULL THEN "Completed lesson"
               WHEN ue.id IS NOT NULL THEN "Enrolled in course"
               ELSE "Registered"
           END as activity,
           COALESCE(qa.attempted_at, ulp.completed_at, ue.enrolled_at, u.created_at) as activity_time
    FROM users u
    LEFT JOIN quiz_attempts qa ON qa.user_id = u.id
    LEFT JOIN user_lesson_progress ulp ON ulp.user_id = u.id
    LEFT JOIN user_enrollments ue ON ue.user_id = u.id
    WHERE u.role = "student"
    ORDER BY activity_time DESC
    LIMIT 10
')->fetchAll();

// Get quiz performance by course
$quizPerformance = db()->query('
    SELECT c.title as course_title, 
           COUNT(qa.id) as attempts,
           ROUND(AVG(qa.score), 1) as avg_score,
           ROUND(AVG(qa.passed) * 100, 1) as pass_rate
    FROM courses c
    JOIN modules m ON m.course_id = c.id
    JOIN quizzes q ON q.module_id = m.id
    LEFT JOIN quiz_attempts qa ON qa.quiz_id = q.id
    WHERE c.status = "published"
    GROUP BY c.id
    ORDER BY c.order_index
')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Analytics Dashboard</h1>
            <span class="overline">REAL-TIME INSIGHTS</span>
        </div>

        <?php render_flash(); ?>

        <!-- Key Metrics Grid -->
        <div class="analytics-grid mb-4">
            <div class="analytics-card">
                <div class="analytics-card-title">Total Students</div>
                <div class="analytics-card-value"><?= $analytics['total_students'] ?></div>
                <div class="analytics-card-change positive">
                    <i class="bi bi-people-fill me-1"></i> Registered users
                </div>
            </div>
            
            <div class="analytics-card">
                <div class="analytics-card-title">Avg Completion Rate</div>
                <div class="analytics-card-value"><?= $analytics['avg_completion_rate'] ?>%</div>
                <div class="analytics-card-change">
                    <i class="bi bi-graph-up me-1"></i> Course progress
                </div>
            </div>
            
            <div class="analytics-card">
                <div class="analytics-card-title">Avg Quiz Score</div>
                <div class="analytics-card-value"><?= $analytics['avg_quiz_score'] ?>%</div>
                <div class="analytics-card-change">
                    <i class="bi bi-trophy me-1"></i> <?= $analytics['pass_rate'] ?>% pass rate
                </div>
            </div>
            
            <div class="analytics-card">
                <div class="analytics-card-title">Eligible Candidates</div>
                <div class="analytics-card-value"><?= $analytics['eligible_candidates'] ?></div>
                <div class="analytics-card-change positive">
                    <i class="bi bi-check-circle-fill me-1"></i> Ready for hackathon
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Course Performance -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">
                            <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                            Course Performance
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" data-testid="course-performance-table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Enrolled</th>
                                        <th>Completed</th>
                                        <th>Completion Rate</th>
                                        <th>Avg Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courseAnalytics as $ca): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-600"><?= h($ca['course']['title']) ?></span>
                                        </td>
                                        <td><?= $ca['enrolled'] ?></td>
                                        <td><?= $ca['completed'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                                    <div class="progress-bar" style="width: <?= $ca['completion_rate'] ?>%"></div>
                                                </div>
                                                <span class="small"><?= $ca['completion_rate'] ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $ca['avg_score'] >= 70 ? 'bg-success' : ($ca['avg_score'] >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                                <?= $ca['avg_score'] ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quiz Performance -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">
                            <i class="bi bi-clipboard-data text-warning me-2"></i>
                            Quiz Performance by Course
                        </h5>
                        <?php foreach ($quizPerformance as $qp): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-600"><?= h($qp['course_title']) ?></span>
                                <span class="badge bg-secondary"><?= $qp['attempts'] ?> attempts</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Average Score</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar" style="width: <?= $qp['avg_score'] ?>%"></div>
                                        </div>
                                        <span class="fw-600"><?= $qp['avg_score'] ?>%</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1">Pass Rate</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar <?= $qp['pass_rate'] >= 70 ? '' : 'bg-warning' ?>" style="width: <?= $qp['pass_rate'] ?>%"></div>
                                        </div>
                                        <span class="fw-600"><?= $qp['pass_rate'] ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="col-lg-4">
                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">
                            <i class="bi bi-lightning-fill text-primary me-2"></i>
                            Quick Stats
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Enrollments</span>
                                <span class="fw-700"><?= $analytics['total_enrollments'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Quiz Attempts</span>
                                <span class="fw-700"><?= $analytics['total_quiz_attempts'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Overall Pass Rate</span>
                                <span class="fw-700 text-success"><?= $analytics['pass_rate'] ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Active Courses</span>
                                <span class="fw-700"><?= count($courseAnalytics) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">
                            <i class="bi bi-activity text-success me-2"></i>
                            Recent Activity
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach (array_slice($recentActivity, 0, 8) as $activity): ?>
                            <div class="d-flex align-items-start gap-2">
                                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.7rem; flex-shrink: 0;">
                                    <?= strtoupper(substr($activity['name'], 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-500 small"><?= h($activity['name']) ?></div>
                                    <div class="text-muted small"><?= h($activity['activity']) ?></div>
                                </div>
                                <div class="text-muted small">
                                    <?= date('M j', strtotime($activity['activity_time'])) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentActivity)): ?>
                            <p class="text-muted text-center py-3">No recent activity</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
