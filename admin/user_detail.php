<?php
/**
 * Admin → single-user learning analytics.
 * Shows one student's progress, quiz/exam history and activity.
 * Reached from All Users / Progress Reports. Distinct from candidate eligibility.
 */
$pageTitle = 'User Analytics';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$uid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$uid) { header('Location: /admin/users.php'); exit; }

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$uid]);
$u = $stmt->fetch();
if (!$u) { set_flash('error', 'User not found.'); header('Location: /admin/users.php'); exit; }

/** Format seconds as "Xh Ym" / "Ym". */
function fmt_duration($seconds): string {
    $seconds = (int)$seconds;
    if ($seconds <= 0) return '0m';
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    return ($h ? $h . 'h ' : '') . $m . 'm';
}
/** "x days ago" style relative time, or em dash. */
function rel_time(?string $ts): string {
    if (!$ts || $ts < '2000-01-01') return '—';
    $t = strtotime($ts);
    if (!$t) return '—';
    $diff = time() - $t;
    if ($diff < 60)    return 'just now';
    if ($diff < 3600)  return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $t);
}

// ── Aggregate stats (scoped to this user) ──
$one = fn(string $sql) => (function ($s, $id) { $q = db()->prepare($s); $q->execute([$id]); return $q->fetchColumn(); })($sql, $uid);

$enrollments   = (int)$one('SELECT COUNT(*) FROM user_enrollments WHERE user_id = ?');
$quizAttempts  = (int)$one('SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ?');
$quizzesPassed = (int)$one('SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND passed = 1');
$avgScore      = $one('SELECT ROUND(AVG(score),1) FROM quiz_attempts WHERE user_id = ?');
$examPasses    = (int)$one('SELECT COUNT(DISTINCT exam_id) FROM final_exam_attempts WHERE user_id = ? AND passed = 1');
$estTime       = (int)$one('SELECT COALESCE(SUM(time_spent),0) FROM user_lesson_progress WHERE user_id = ?')
               + (int)$one('SELECT COALESCE(SUM(time_taken),0) FROM quiz_attempts WHERE user_id = ?');

// Lessons completed within PUBLISHED courses + overall completion %
$totalPubLessons = (int)db()->query("SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id JOIN courses c ON m.course_id=c.id WHERE c.status='published'")->fetchColumn();
$lessonsDone = (int)$one("SELECT COUNT(*) FROM user_lesson_progress p JOIN lessons l ON p.lesson_id=l.id JOIN modules m ON l.module_id=m.id JOIN courses c ON m.course_id=c.id WHERE p.user_id=? AND c.status='published'");
$overallPct  = $totalPubLessons > 0 ? round($lessonsDone / $totalPubLessons * 100) : 0;

$lastActive = db()->prepare("SELECT MAX(x) FROM (
    SELECT last_activity AS x FROM users WHERE id = :a
    UNION ALL SELECT MAX(completed_at) FROM user_lesson_progress WHERE user_id = :b
    UNION ALL SELECT MAX(attempted_at) FROM quiz_attempts WHERE user_id = :c
)");
$lastActive->execute([':a' => $uid, ':b' => $uid, ':c' => $uid]);
$lastActive = $lastActive->fetchColumn();

// Per-course progress
$courses = get_all_published_courses();
$coursesCompleted = 0;
foreach ($courses as $c) { if (is_course_complete($uid, (int)$c['id'])) $coursesCompleted++; }

// Quiz attempt history
$quizHistory = db()->prepare("
    SELECT qa.score, qa.passed, qa.attempted_at, q.title AS quiz_title, c.title AS course_title
    FROM quiz_attempts qa
    JOIN quizzes q ON q.id = qa.quiz_id
    JOIN modules m ON m.id = q.module_id
    JOIN courses c ON c.id = m.course_id
    WHERE qa.user_id = ?
    ORDER BY qa.attempted_at DESC
    LIMIT 25");
$quizHistory->execute([$uid]);
$quizHistory = $quizHistory->fetchAll();

// Final exam attempts
$examHistory = db()->prepare("
    SELECT fa.score, fa.passed, fa.completed_at, fa.started_at, c.title AS course_title
    FROM final_exam_attempts fa
    JOIN final_exams fe ON fe.id = fa.exam_id
    JOIN courses c ON c.id = fe.course_id
    WHERE fa.user_id = ?
    ORDER BY COALESCE(fa.completed_at, fa.started_at) DESC
    LIMIT 15");
$examHistory->execute([$uid]);
$examHistory = $examHistory->fetchAll();

// Recent activity (derived from lessons + quizzes)
$activity = db()->prepare("
    SELECT 'lesson' AS kind, l.title AS title, c.title AS course, p.completed_at AS at
    FROM user_lesson_progress p
    JOIN lessons l ON l.id = p.lesson_id
    JOIN modules m ON m.id = l.module_id
    JOIN courses c ON c.id = m.course_id
    WHERE p.user_id = :u1
    UNION ALL
    SELECT 'quiz' AS kind, q.title, c.title, qa.attempted_at
    FROM quiz_attempts qa
    JOIN quizzes q ON q.id = qa.quiz_id
    JOIN modules m ON m.id = q.module_id
    JOIN courses c ON c.id = m.course_id
    WHERE qa.user_id = :u2
    ORDER BY at DESC
    LIMIT 15");
$activity->execute([':u1' => $uid, ':u2' => $uid]);
$activity = $activity->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0" style="font-size:0.85rem">
                <li class="breadcrumb-item"><a href="/admin/progress.php">Progress Reports</a></li>
                <li class="breadcrumb-item"><a href="/admin/users.php">All Users</a></li>
                <li class="breadcrumb-item active"><?= h($u['name']) ?></li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <!-- Profile header -->
        <div class="card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon blue" style="width:56px;height:56px;font-size:1.3rem">
                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="admin-page-title mb-1" style="font-size:1.4rem"><?= h($u['name']) ?>
                            <span class="badge bg-<?= $u['role'] === 'student' ? 'secondary' : 'primary' ?> ms-1" style="font-size:0.65rem;vertical-align:middle"><?= h($u['role']) ?></span>
                            <?php if (!$u['is_active']): ?><span class="badge bg-danger ms-1" style="font-size:0.65rem;vertical-align:middle">inactive</span><?php endif; ?>
                        </h1>
                        <div class="text-muted small">
                            <i class="bi bi-envelope me-1"></i><?= h($u['email']) ?>
                            <?php if (!empty($u['country'])): ?> &nbsp;·&nbsp; <i class="bi bi-geo-alt me-1"></i><?= h($u['country']) ?><?php endif; ?>
                            &nbsp;·&nbsp; Joined <?= date('M j, Y', strtotime($u['created_at'])) ?>
                            &nbsp;·&nbsp; Last active <?= h(rel_time($lastActive)) ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="/admin/edit_user.php?id=<?= $u['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <a href="/admin/candidates.php?q=<?= urlencode($u['email']) ?>" class="btn btn-outline-secondary btn-sm" title="HackathonAfrica eligibility review"><i class="bi bi-person-check me-1"></i>Eligibility</a>
                </div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
            <?php
            $cards = [
                ['Overall completion', $overallPct . '%', 'graph-up-arrow', 'green'],
                ['Lessons completed', $lessonsDone . ' / ' . $totalPubLessons, 'file-text', 'blue'],
                ['Courses completed', $coursesCompleted . ' / ' . count($courses), 'collection', 'purple'],
                ['Quizzes passed', $quizzesPassed . ' / ' . $quizAttempts, 'question-circle', 'orange'],
                ['Avg quiz score', ($avgScore !== null ? $avgScore . '%' : '—'), 'bar-chart', 'blue'],
                ['Final exams passed', (string)$examPasses, 'shield-check', 'green'],
                ['Courses enrolled', (string)$enrollments, 'bookmark', 'purple'],
                ['Est. time on content', fmt_duration($estTime), 'clock-history', 'orange'],
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

        <div class="row g-4">
            <!-- Per-course progress -->
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3"><i class="bi bi-collection me-2"></i>Course progress</h5>
                        <?php if (empty($courses)): ?>
                            <p class="text-muted mb-0">No published courses.</p>
                        <?php else: foreach ($courses as $c):
                            $cid = (int)$c['id'];
                            $prog = get_course_progress($uid, $cid);
                            $enrolled = is_enrolled($uid, $cid);
                            $complete = is_course_complete($uid, $cid);
                            $modules = get_modules_for_course($cid);
                        ?>
                        <div class="mb-3 pb-3 border-bottom" style="border-color:var(--border)!important">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-600"><?= h($c['title']) ?>
                                    <?php if ($complete): ?><i class="bi bi-check-circle-fill text-success ms-1" title="Completed"></i>
                                    <?php elseif (!$enrolled && $prog['done'] === 0): ?><span class="badge bg-light text-muted ms-1" style="font-size:0.6rem">not started</span><?php endif; ?>
                                </span>
                                <span class="small text-muted"><?= $prog['done'] ?>/<?= $prog['total'] ?> lessons · <?= $prog['percent'] ?>%</span>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar <?= $complete ? 'bg-success' : '' ?>" style="width:<?= $prog['percent'] ?>%"></div>
                            </div>
                            <?php if ($prog['done'] > 0 || $enrolled): ?>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <?php foreach ($modules as $mod):
                                    $mc = get_module_completion($uid, (int)$mod['id']);
                                    $quiz = get_quiz_for_module((int)$mod['id']);
                                    $qa = $quiz ? get_best_quiz_attempt($uid, (int)$quiz['id']) : null;
                                    $cls = $mc['complete'] ? 'success' : ($mc['lessons_done'] > 0 ? 'warning' : 'light');
                                    $txt = $cls === 'light' ? 'text-muted' : '';
                                ?>
                                <span class="badge bg-<?= $cls ?> <?= $txt ?>" style="font-weight:500"
                                      title="<?= h($mod['title']) ?>: <?= $mc['lessons_done'] ?>/<?= $mc['lessons_total'] ?> lessons<?= $qa ? ', quiz ' . $qa['score'] . '%' : '' ?>">
                                    <?= $mc['lessons_done'] ?>/<?= $mc['lessons_total'] ?><?php if ($qa): ?> · <?= $qa['score'] ?>%<?php endif; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Quiz history -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3"><i class="bi bi-question-circle me-2"></i>Quiz attempts</h5>
                        <?php if (empty($quizHistory)): ?>
                            <p class="text-muted mb-0">No quiz attempts yet.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Quiz</th><th>Course</th><th>Score</th><th>Result</th><th>When</th></tr></thead>
                                <tbody>
                                <?php foreach ($quizHistory as $qh): ?>
                                    <tr>
                                        <td><?= h($qh['quiz_title']) ?></td>
                                        <td class="text-muted small"><?= h($qh['course_title']) ?></td>
                                        <td><?= (int)$qh['score'] ?>%</td>
                                        <td><span class="badge bg-<?= $qh['passed'] ? 'success' : 'danger' ?>"><?= $qh['passed'] ? 'Passed' : 'Failed' ?></span></td>
                                        <td class="text-muted small"><?= h(rel_time($qh['attempted_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($examHistory)): ?>
                <!-- Final exam history -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3"><i class="bi bi-shield-check me-2"></i>Final exam attempts</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>Course</th><th>Score</th><th>Result</th><th>When</th></tr></thead>
                                <tbody>
                                <?php foreach ($examHistory as $eh): ?>
                                    <tr>
                                        <td><?= h($eh['course_title']) ?></td>
                                        <td><?= (int)$eh['score'] ?>%</td>
                                        <td><span class="badge bg-<?= $eh['passed'] ? 'success' : 'danger' ?>"><?= $eh['passed'] ? 'Passed' : 'Failed' ?></span></td>
                                        <td class="text-muted small"><?= h(rel_time($eh['completed_at'] ?: $eh['started_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Activity timeline -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3"><i class="bi bi-activity me-2"></i>Recent activity</h5>
                        <?php if (empty($activity)): ?>
                            <p class="text-muted mb-0">No learning activity recorded yet.</p>
                        <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($activity as $a): ?>
                            <li class="d-flex gap-2 pb-3 mb-0">
                                <i class="bi bi-<?= $a['kind'] === 'quiz' ? 'question-circle text-warning' : 'check-circle text-success' ?> mt-1"></i>
                                <div>
                                    <div class="small">
                                        <?= $a['kind'] === 'quiz' ? 'Attempted quiz' : 'Completed lesson' ?>
                                        <strong><?= h($a['title']) ?></strong>
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem">
                                        <?= h($a['course']) ?> · <?= h(rel_time($a['at'])) ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
