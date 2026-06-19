<?php
/**
 * Admin → Progress Reports.
 * Platform-wide learning traction across ALL students: engagement, per-course
 * progress, and a per-learner table. This is general learning analytics and is
 * intentionally separate from "Review Candidates" (HackathonAfrica eligibility).
 */
$pageTitle = 'Progress Reports';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

function rel_time2(?string $ts): string {
    if (!$ts || $ts < '2000-01-01') return 'never';
    $t = strtotime($ts);
    if (!$t) return 'never';
    $diff = time() - $t;
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $t);
}

// ── Summary / engagement ──
$totalStudents = count_users();
$totalLessonsDone = (int)db()->query('SELECT COUNT(*) FROM user_lesson_progress')->fetchColumn();
$totalQuizAttempts = (int)db()->query('SELECT COUNT(*) FROM quiz_attempts')->fetchColumn();
$avgQuizScore = db()->query('SELECT ROUND(AVG(score),1) FROM quiz_attempts')->fetchColumn();
$passRate = db()->query('SELECT ROUND(AVG(passed)*100) FROM quiz_attempts')->fetchColumn();

$activeWindow = function (int $days): int {
    $sql = "SELECT COUNT(*) FROM users u WHERE u.role='student' AND (
        u.last_activity >= datetime('now','-$days days')
        OR EXISTS(SELECT 1 FROM user_lesson_progress p WHERE p.user_id=u.id AND p.completed_at >= datetime('now','-$days days'))
        OR EXISTS(SELECT 1 FROM quiz_attempts qa WHERE qa.user_id=u.id AND qa.attempted_at >= datetime('now','-$days days'))
    )";
    return (int)db()->query($sql)->fetchColumn();
};
$active7  = $activeWindow(7);
$active30 = $activeWindow(30);

// ── Per-course traction ──
$courseTraction = db()->query("
    SELECT c.id, c.title,
        (SELECT COUNT(*) FROM user_enrollments ue WHERE ue.course_id=c.id) AS enrolled,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id WHERE m.course_id=c.id) AS total_lessons,
        (SELECT COUNT(*) FROM user_lesson_progress p
            JOIN lessons l ON l.id=p.lesson_id JOIN modules m ON m.id=l.module_id
            WHERE m.course_id=c.id) AS lessons_done,
        (SELECT ROUND(AVG(qa.score),1) FROM quiz_attempts qa
            JOIN quizzes q ON q.id=qa.quiz_id JOIN modules m ON m.id=q.module_id
            WHERE m.course_id=c.id) AS avg_score,
        (SELECT ROUND(AVG(qa.passed)*100) FROM quiz_attempts qa
            JOIN quizzes q ON q.id=qa.quiz_id JOIN modules m ON m.id=q.module_id
            WHERE m.course_id=c.id) AS pass_rate
    FROM courses c WHERE c.status='published' ORDER BY c.order_index
")->fetchAll();

// ── Per-learner table (search + sort + pagination) ──
$search = trim($_GET['q'] ?? '');
$sort   = $_GET['sort'] ?? 'active';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = "u.role='student'";
$params = [];
if ($search !== '') {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderMap = [
    'active'   => 'last_active DESC',
    'lessons'  => 'lessons_done DESC',
    'score'    => 'avg_quiz_score DESC',
    'newest'   => 'u.created_at DESC',
    'name'     => 'u.name ASC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['active'];

$totalRows = (function ($where, $params) {
    $st = db()->prepare("SELECT COUNT(*) FROM users u WHERE $where");
    $st->execute($params);
    return (int)$st->fetchColumn();
})($where, $params);
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$rowsSql = "
    SELECT u.id, u.name, u.email, u.country, u.created_at,
        (SELECT COUNT(*) FROM user_enrollments WHERE user_id=u.id) AS enrollments,
        (SELECT COUNT(*) FROM user_lesson_progress p
            JOIN lessons l ON l.id=p.lesson_id JOIN modules m ON m.id=l.module_id
            JOIN courses c ON c.id=m.course_id AND c.status='published'
            WHERE p.user_id=u.id) AS lessons_done,
        (SELECT COUNT(*) FROM quiz_attempts WHERE user_id=u.id) AS quiz_attempts,
        (SELECT COUNT(*) FROM quiz_attempts WHERE user_id=u.id AND passed=1) AS quizzes_passed,
        (SELECT ROUND(AVG(score),1) FROM quiz_attempts WHERE user_id=u.id) AS avg_quiz_score,
        MAX(
            COALESCE(u.last_activity,'1970-01-01'),
            COALESCE((SELECT MAX(completed_at) FROM user_lesson_progress WHERE user_id=u.id),'1970-01-01'),
            COALESCE((SELECT MAX(attempted_at) FROM quiz_attempts WHERE user_id=u.id),'1970-01-01')
        ) AS last_active
    FROM users u
    WHERE $where
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset";
$rowsStmt = db()->prepare($rowsSql);
$rowsStmt->execute($params);
$rows = $rowsStmt->fetchAll();

$totalPubLessons = (int)db()->query("SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id JOIN courses c ON m.course_id=c.id WHERE c.status='published'")->fetchColumn();

$qs = fn(array $over) => http_build_query(array_merge(['q' => $search, 'sort' => $sort, 'page' => $page], $over));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
            <h1 class="admin-page-title mb-0">Progress Reports</h1>
            <a href="/admin/analytics.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-graph-up me-1"></i>Platform Analytics</a>
        </div>
        <p class="text-muted">Learning traction across all students — engagement, course progress and per-learner detail. For HackathonAfrica eligibility decisions, use <a href="/admin/candidates.php">Review Candidates</a>.</p>

        <?php render_flash(); ?>

        <!-- Engagement summary -->
        <div class="row g-3 mb-4">
            <?php
            $cards = [
                ['Students', number_format($totalStudents), 'people', 'blue'],
                ['Active (7 days)', number_format($active7), 'lightning-charge', 'green'],
                ['Active (30 days)', number_format($active30), 'calendar-check', 'green'],
                ['Lessons completed', number_format($totalLessonsDone), 'file-text', 'purple'],
                ['Quiz attempts', number_format($totalQuizAttempts), 'question-circle', 'orange'],
                ['Avg quiz score', ($avgQuizScore !== null ? $avgQuizScore . '%' : '—'), 'bar-chart', 'blue'],
                ['Quiz pass rate', ($passRate !== null ? $passRate . '%' : '—'), 'check2-circle', 'green'],
                ['Active rate (30d)', ($totalStudents ? round($active30 / $totalStudents * 100) : 0) . '%', 'activity', 'purple'],
            ];
            foreach ($cards as [$label, $value, $icon, $color]): ?>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon <?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></div>
                        <div>
                            <div class="fw-700" style="font-size:1.25rem;line-height:1"><?= h($value) ?></div>
                            <div class="text-muted small"><?= h($label) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Per-course traction -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="fw-700 mb-3"><i class="bi bi-collection me-2"></i>Course traction</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr>
                            <th>Course</th><th>Enrolled</th><th style="min-width:160px">Avg progress</th>
                            <th>Avg quiz</th><th>Pass rate</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($courseTraction as $ct):
                            // Average progress = total lessons done / (enrolled * lessons in course)
                            $denom = (int)$ct['enrolled'] * (int)$ct['total_lessons'];
                            $avgProg = $denom > 0 ? round($ct['lessons_done'] / $denom * 100) : 0;
                        ?>
                            <tr>
                                <td class="fw-600"><?= h($ct['title']) ?></td>
                                <td><?= (int)$ct['enrolled'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:7px">
                                            <div class="progress-bar" style="width:<?= $avgProg ?>%"></div>
                                        </div>
                                        <span class="small text-muted" style="width:34px"><?= $avgProg ?>%</span>
                                    </div>
                                </td>
                                <td><?= $ct['avg_score'] !== null ? (int)$ct['avg_score'] . '%' : '—' ?></td>
                                <td><?= $ct['pass_rate'] !== null ? (int)$ct['pass_rate'] . '%' : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courseTraction)): ?>
                            <tr><td colspan="5" class="text-muted text-center py-3">No published courses.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Per-learner table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="fw-700 mb-0"><i class="bi bi-person-lines-fill me-2"></i>Learners
                        <span class="text-muted fw-400" style="font-size:0.85rem">(<?= number_format($totalRows) ?>)</span>
                    </h5>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <a href="/actions/admin/export_progress.php?<?= h(http_build_query(['q' => $search, 'sort' => $sort])) ?>"
                           class="btn btn-success btn-sm" title="Export the current learner list to CSV">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </a>
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="sort" value="<?= h($sort) ?>">
                            <input type="search" name="q" value="<?= h($search) ?>" class="form-control form-control-sm" style="width:200px" placeholder="Search name or email">
                            <button class="btn btn-primary btn-sm">Search</button>
                            <?php if ($search): ?><a href="/admin/progress.php" class="btn btn-outline-secondary btn-sm">Clear</a><?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php
                $sortLinks = ['active' => 'Last active', 'lessons' => 'Lessons', 'score' => 'Avg score', 'newest' => 'Newest', 'name' => 'Name'];
                ?>
                <div class="mb-2 small text-muted">
                    Sort:
                    <?php foreach ($sortLinks as $key => $lbl): ?>
                        <a href="?<?= h($qs(['sort' => $key, 'page' => 1])) ?>" class="ms-1 <?= $sort === $key ? 'fw-700 text-decoration-none' : 'text-muted' ?>"><?= $lbl ?></a><?= $key !== array_key_last($sortLinks) ? ' ·' : '' ?>
                    <?php endforeach; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr>
                            <th>Learner</th><th>Lessons</th><th>Quizzes</th><th>Avg score</th>
                            <th>Enrolled</th><th>Last active</th><th></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r):
                            $pct = $totalPubLessons > 0 ? round($r['lessons_done'] / $totalPubLessons * 100) : 0;
                        ?>
                            <tr>
                                <td>
                                    <a href="/admin/user_detail.php?id=<?= $r['id'] ?>" class="fw-600 text-decoration-none"><?= h($r['name']) ?></a>
                                    <div class="text-muted" style="font-size:0.75rem"><?= h($r['email']) ?><?= $r['country'] ? ' · ' . h($r['country']) : '' ?></div>
                                </td>
                                <td style="min-width:130px">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;min-width:50px">
                                            <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <span class="small text-muted"><?= (int)$r['lessons_done'] ?></span>
                                    </div>
                                </td>
                                <td class="small"><?= (int)$r['quizzes_passed'] ?>/<?= (int)$r['quiz_attempts'] ?></td>
                                <td><?= $r['avg_quiz_score'] !== null ? (int)$r['avg_quiz_score'] . '%' : '—' ?></td>
                                <td><?= (int)$r['enrollments'] ?></td>
                                <td class="text-muted small"><?= h(rel_time2($r['last_active'])) ?></td>
                                <td><a href="/admin/user_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="View analytics"><i class="bi bi-graph-up"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="7" class="text-muted text-center py-3">No learners found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <nav class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Page <?= $page ?> of <?= $totalPages ?></span>
                    <div class="btn-group">
                        <?php if ($page > 1): ?><a class="btn btn-sm btn-outline-secondary" href="?<?= h($qs(['page' => $page - 1])) ?>">&laquo; Prev</a><?php endif; ?>
                        <?php if ($page < $totalPages): ?><a class="btn btn-sm btn-outline-secondary" href="?<?= h($qs(['page' => $page + 1])) ?>">Next &raquo;</a><?php endif; ?>
                    </div>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
