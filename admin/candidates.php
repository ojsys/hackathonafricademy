<?php
$pageTitle = 'Candidate Review';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verify_csrf();
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($userId && in_array($status, ['pending', 'eligible', 'needs_review', 'rejected', 'to_be_decided'])) {
        $existing = db()->prepare('SELECT id FROM candidate_reviews WHERE user_id = ?');
        $existing->execute([$userId]);

        if ($existing->fetch()) {
            $stmt = db()->prepare('UPDATE candidate_reviews SET eligibility_status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
            $stmt->execute([$status, $notes, current_user()['id'], $userId]);
        } else {
            $stmt = db()->prepare('INSERT INTO candidate_reviews (user_id, eligibility_status, admin_notes, reviewed_by, reviewed_at, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
            $stmt->execute([$userId, $status, $notes, current_user()['id']]);
        }

        set_flash('success', 'Candidate status updated.');
    }
    header('Location: /admin/candidates.php');
    exit;
}

// Filter + pagination
$statusFilter = $_GET['status'] ?? 'all';
$filters = [
    'q'           => trim($_GET['q'] ?? ''),
    'country'     => trim($_GET['country'] ?? ''),
    'experience'  => $_GET['experience'] ?? '',
    'min_score'   => $_GET['min_score'] ?? '',
    'min_quiz'    => $_GET['min_quiz'] ?? '',
    'sort'        => $_GET['sort'] ?? 'score_desc',
    'has_github'  => !empty($_GET['has_github']) ? '1' : '',
    'min_lessons' => $_GET['min_lessons'] ?? '',
];
$perPage         = 12;
$page            = max(1, (int) ($_GET['page'] ?? 1));
$offset          = ($page - 1) * $perPage;
$totalCandidates = count_candidates_for_review($statusFilter, $filters);
$totalPages      = (int) ceil($totalCandidates / $perPage);
$candidates      = get_candidates_for_review($statusFilter, $perPage, $offset, $filters);
$courses         = get_all_published_courses();

$paginBaseParams = array_filter(array_merge(['status' => $statusFilter], $filters));
$paginationBase  = '/admin/candidates.php?' . http_build_query($paginBaseParams) . '&';

// Stats
$stats = [
    'total'          => db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn(),
    'eligible'       => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "eligible"')->fetchColumn(),
    'to_be_decided'  => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "to_be_decided"')->fetchColumn(),
    'needs_review'   => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "needs_review"')->fetchColumn(),
    'pending'        => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "pending"')->fetchColumn(),
    'rejected'       => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "rejected"')->fetchColumn(),
];

$statusIcons = [
    'eligible'       => 'bi-check-circle-fill',
    'pending'        => 'bi-hourglass-split',
    'needs_review'   => 'bi-eye-fill',
    'rejected'       => 'bi-x-circle-fill',
    'to_be_decided'  => 'bi-slash-circle-fill',
];
$statusLabels = [
    'eligible'       => 'Eligible',
    'pending'        => 'Pending',
    'needs_review'   => 'Needs Review',
    'rejected'       => 'Rejected',
    'to_be_decided'  => 'To be Decided',
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="admin-page-title mb-1">Candidate Review</h1>
                <p class="text-muted mb-0">Review and manage HackathonAfrica 3.0 applicants</p>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a href="/actions/admin/export_candidates.php?status=<?= h($statusFilter) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-funnel me-1"></i> Pipeline
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Ranking</h6></li>
                        <li>
                            <form action="/actions/admin/shortlist_candidates.php" method="POST" class="px-3 pb-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="run_ranking">
                                <button class="btn btn-sm btn-outline-secondary w-100" onclick="return confirm('Recalculate composite scores for all candidates?')">
                                    <i class="bi bi-calculator me-1"></i> Recalculate Scores
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="/actions/admin/shortlist_candidates.php" method="POST" class="px-3 pb-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="shortlist">
                                <button class="btn btn-sm btn-primary w-100" onclick="return confirm('Replace current shortlist with top <?= get_setting('shortlist_limit','100') ?> eligible candidates by composite score?')">
                                    <i class="bi bi-list-stars me-1"></i> Run Shortlisting
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Emails</h6></li>
                        <li>
                            <form action="/actions/admin/shortlist_candidates.php" method="POST" class="px-3 pb-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="invite">
                                <button class="btn btn-sm btn-success w-100" onclick="return confirm('Send bootcamp invitations to all shortlisted candidates not yet invited?')">
                                    <i class="bi bi-envelope-check me-1"></i> Send Invitations
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="/actions/admin/shortlist_candidates.php" method="POST" class="px-3 pb-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="notify_not_selected">
                                <button class="btn btn-sm btn-outline-secondary w-100" onclick="return confirm('Send appreciation emails to non-shortlisted candidates?')">
                                    <i class="bi bi-envelope me-1"></i> Notify Not Selected
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <span class="eligibility-status eligible">
                    <i class="bi bi-trophy-fill"></i>
                    <?= $stats['eligible'] ?> Eligible
                </span>
            </div>
        </div>

        <?php render_flash(); ?>

        <!-- Status Filter Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <a href="?status=all" class="card text-decoration-none <?= $statusFilter === 'all' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1"><?= $stats['total'] ?></div>
                        <div class="text-muted small">All</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="?status=eligible" class="card text-decoration-none <?= $statusFilter === 'eligible' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1 text-success"><?= $stats['eligible'] ?></div>
                        <div class="text-muted small">Eligible</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="?status=to_be_decided" class="card text-decoration-none <?= $statusFilter === 'to_be_decided' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1" style="color:#f97316"><?= $stats['to_be_decided'] ?></div>
                        <div class="text-muted small">To be Decided</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="?status=needs_review" class="card text-decoration-none <?= $statusFilter === 'needs_review' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1 text-warning"><?= $stats['needs_review'] ?></div>
                        <div class="text-muted small">Needs Review</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="?status=pending" class="card text-decoration-none <?= $statusFilter === 'pending' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1"><?= $stats['pending'] ?></div>
                        <div class="text-muted small">Pending</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="?status=rejected" class="card text-decoration-none <?= $statusFilter === 'rejected' ? 'border-primary' : '' ?>">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1 text-danger"><?= $stats['rejected'] ?></div>
                        <div class="text-muted small">Rejected</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body p-3">
                <form method="GET">
                    <input type="hidden" name="status" value="<?= h($statusFilter) ?>">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label mb-1 small">Search</label>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Name or email" value="<?= h($filters['q']) ?>">
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <label class="form-label mb-1 small">Country</label>
                            <input type="text" name="country" class="form-control form-control-sm" placeholder="e.g. Nigeria" value="<?= h($filters['country']) ?>">
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <label class="form-label mb-1 small">Experience</label>
                            <select name="experience" class="form-select form-select-sm">
                                <option value="">Any</option>
                                <option value="none" <?= $filters['experience'] === 'none' ? 'selected' : '' ?>>Beginner</option>
                                <option value="lt1"  <?= $filters['experience'] === 'lt1'  ? 'selected' : '' ?>>&lt; 1 year</option>
                                <option value="1-2"  <?= $filters['experience'] === '1-2'  ? 'selected' : '' ?>>1–2 years</option>
                                <option value="3-5"  <?= $filters['experience'] === '3-5'  ? 'selected' : '' ?>>3–5 years</option>
                                <option value="5+"   <?= $filters['experience'] === '5+'   ? 'selected' : '' ?>>5+ years</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-2">
                            <label class="form-label mb-1 small">Sort by</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="score_desc"   <?= $filters['sort'] === 'score_desc'   ? 'selected' : '' ?>>Top total score</option>
                                <option value="quiz_desc"    <?= $filters['sort'] === 'quiz_desc'    ? 'selected' : '' ?>>Top quiz score</option>
                                <option value="lessons_desc" <?= $filters['sort'] === 'lessons_desc' ? 'selected' : '' ?>>Most lessons done</option>
                                <option value="joined_desc"  <?= $filters['sort'] === 'joined_desc'  ? 'selected' : '' ?>>Newest joined</option>
                                <option value="joined_asc"   <?= $filters['sort'] === 'joined_asc'   ? 'selected' : '' ?>>Oldest joined</option>
                                <option value="name_asc"     <?= $filters['sort'] === 'name_asc'     ? 'selected' : '' ?>>Name A–Z</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label mb-1 small">Min Lessons</label>
                            <input type="number" name="min_lessons" class="form-control form-control-sm" placeholder="e.g. 5" min="0" value="<?= h($filters['min_lessons']) ?>">
                        </div>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Min Total Score</label>
                            <input type="number" name="min_score" class="form-control form-control-sm" placeholder="0–100" min="0" max="100" value="<?= h($filters['min_score']) ?>">
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Min Quiz Score</label>
                            <input type="number" name="min_quiz" class="form-control form-control-sm" placeholder="0–100" min="0" max="100" value="<?= h($filters['min_quiz']) ?>">
                        </div>
                        <div class="col-auto d-flex align-items-end gap-2">
                            <div class="form-check mb-0" style="padding-top:1.6rem">
                                <input class="form-check-input" type="checkbox" name="has_github" id="has_github" value="1" <?= $filters['has_github'] ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="has_github">Has GitHub</label>
                            </div>
                        </div>
                        <div class="col-auto" style="padding-top:1.6rem">
                            <button class="btn btn-primary btn-sm">Apply</button>
                            <a href="/admin/candidates.php?status=<?= h($statusFilter) ?>" class="btn btn-outline-secondary btn-sm ms-1">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Result summary -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small">
                <?= $totalCandidates ?> candidate<?= $totalCandidates !== 1 ? 's' : '' ?> found
            </span>
        </div>

        <!-- Candidates List -->
        <div class="d-flex flex-column gap-3">
            <?php foreach ($candidates as $candidate):
                $status = $candidate['eligibility_status'] ?? 'pending';

                // Courses completed for this candidate
                $coursesCompleted = 0;
                $notCompletedCourses = [];
                foreach ($courses as $c) {
                    if (is_course_complete($candidate['id'], $c['id'])) {
                        $coursesCompleted++;
                    } else {
                        $notCompletedCourses[] = $c['title'];
                    }
                }

                // Qualifying exam attempts
                $qualStmt = db()->prepare('SELECT id, percentage, passed, started_at, completed_at, time_taken FROM qualifying_attempts WHERE user_id = ? AND completed_at IS NOT NULL ORDER BY completed_at DESC');
                $qualStmt->execute([$candidate['id']]);
                $qualAttempts = $qualStmt->fetchAll();
                $qualAttemptCount = count($qualAttempts);
                $latestQual = $qualAttempts[0] ?? null;

                // Per-course exam breakdown
                $courseExams = [];
                foreach ($courses as $c) {
                    $exam = get_final_exam_for_course($c['id']);
                    $best = $exam ? get_best_exam_attempt($candidate['id'], $exam['id']) : null;
                    $allAttemptsStmt = $exam ? db()->prepare('SELECT COUNT(*) FROM final_exam_attempts WHERE user_id = ? AND exam_id = ?') : null;
                    if ($allAttemptsStmt) {
                        $allAttemptsStmt->execute([$candidate['id'], $exam['id']]);
                        $attemptCount = (int)$allAttemptsStmt->fetchColumn();
                    } else {
                        $attemptCount = 0;
                    }
                    $courseExams[] = [
                        'title'         => $c['title'],
                        'has_exam'      => (bool)$exam,
                        'best_score'    => $best ? (int)$best['score'] : null,
                        'passed'        => $best ? (bool)$best['passed'] : false,
                        'attempt_count' => $attemptCount,
                        'completed'     => is_course_complete($candidate['id'], $c['id']),
                    ];
                }

                // Pending reason analysis
                $pendingReasons = [];
                if ($status === 'pending') {
                    if ($coursesCompleted === 0) {
                        $pendingReasons[] = 'Has not completed any course';
                    } else {
                        foreach ($notCompletedCourses as $t) {
                            $pendingReasons[] = 'Course not completed: ' . $t;
                        }
                    }
                    if ($qualAttemptCount === 0) {
                        $pendingReasons[] = 'Has not attempted the qualifying exam';
                    }
                }

                // Proctor image count
                $procStmt = db()->prepare('SELECT COUNT(*) FROM proctor_images WHERE user_id = ?');
                $procStmt->execute([$candidate['id']]);
                $proctorImageCount = (int)$procStmt->fetchColumn();

                $qualScore     = $candidate['qualifying_score'] ?? null;
                $qualPassed    = isset($candidate['qualifying_passed']) ? (bool)$candidate['qualifying_passed'] : null;
                $avgExamScore  = $candidate['avg_exam_score'] ?? null;
                $compositeScore = $candidate['composite_score'] ?? null;
            ?>
            <div class="candidate-card" data-testid="candidate-card-<?= $candidate['id'] ?>">
                <div class="candidate-header">
                    <div class="candidate-avatar">
                        <?= strtoupper(substr($candidate['name'], 0, 1)) ?>
                    </div>
                    <div class="candidate-info flex-grow-1">
                        <h4><?= h($candidate['name']) ?></h4>
                        <p><?= h($candidate['email']) ?></p>
                    </div>
                    <div class="eligibility-status <?= h($status) ?>">
                        <i class="bi <?= $statusIcons[$status] ?? 'bi-question-circle' ?>"></i>
                        <?= $statusLabels[$status] ?? 'Unknown' ?>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="candidate-stats">
                    <div class="candidate-stat">
                        <div class="candidate-stat-value"><?= $coursesCompleted ?>/<?= count($courses) ?></div>
                        <div class="candidate-stat-label">Courses</div>
                    </div>
                    <div class="candidate-stat">
                        <div class="candidate-stat-value"><?= $candidate['lessons_completed'] ?? 0 ?></div>
                        <div class="candidate-stat-label">Lessons</div>
                    </div>
                    <div class="candidate-stat">
                        <div class="candidate-stat-value"><?= round($candidate['avg_quiz_score'] ?? 0) ?>%</div>
                        <div class="candidate-stat-label">Quiz Avg</div>
                    </div>
                    <div class="candidate-stat">
                        <div class="candidate-stat-value"><?= $avgExamScore !== null ? round($avgExamScore) . '%' : '—' ?></div>
                        <div class="candidate-stat-label">Final Exam</div>
                    </div>
                    <div class="candidate-stat">
                        <?php if ($qualScore !== null): ?>
                        <div class="candidate-stat-value <?= $qualPassed ? 'text-success' : 'text-danger' ?>">
                            <?= $qualScore ?>%
                        </div>
                        <?php else: ?>
                        <div class="candidate-stat-value text-muted">—</div>
                        <?php endif; ?>
                        <div class="candidate-stat-label">Qualifying</div>
                    </div>
                    <div class="candidate-stat">
                        <div class="candidate-stat-value <?= $compositeScore !== null && $compositeScore >= 75 ? 'text-success' : '' ?>">
                            <?= $compositeScore !== null ? round($compositeScore) . '%' : '—' ?>
                        </div>
                        <div class="candidate-stat-label">Composite</div>
                    </div>
                </div>

                <!-- Pending reason block -->
                <?php if ($status === 'pending' && !empty($pendingReasons)): ?>
                <div class="d-flex align-items-start gap-2 px-1 py-2 rounded mb-1" style="background:rgba(255,204,0,0.07);border:1px solid rgba(255,204,0,0.2)">
                    <i class="bi bi-info-circle text-warning mt-1" style="flex-shrink:0"></i>
                    <div class="small text-muted">
                        <strong class="text-warning">Why pending?</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <?php foreach ($pendingReasons as $r): ?>
                            <li><?= h($r) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Qualifying exam result strip -->
                <?php if ($latestQual): ?>
                <div class="d-flex align-items-center gap-2 small px-1 py-1 rounded mb-1" style="background:var(--surface-hover)">
                    <i class="bi bi-shield-check<?= $latestQual['passed'] ? ' text-success' : '-fill text-danger' ?>"></i>
                    <span class="text-muted">Qualifying exam:</span>
                    <strong class="<?= $latestQual['passed'] ? 'text-success' : 'text-danger' ?>">
                        <?= $latestQual['passed'] ? 'Passed' : 'Failed' ?> — <?= $latestQual['percentage'] ?>%
                    </strong>
                    <span class="text-muted ms-1">(<?= $qualAttemptCount ?> attempt<?= $qualAttemptCount !== 1 ? 's' : '' ?>, last: <?= date('M j Y', strtotime($latestQual['completed_at'])) ?>)</span>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mt-2">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($candidate['city'] || $candidate['country']): ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= h(trim(($candidate['city'] ?? '') . ', ' . ($candidate['country'] ?? ''), ', ')) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($candidate['years_experience']): ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-briefcase me-1"></i>
                            <?= h($candidate['years_experience']) ?> exp
                        </span>
                        <?php endif; ?>
                        <?php if ($candidate['github_url']): ?>
                        <a href="<?= h($candidate['github_url']) ?>" target="_blank" class="badge bg-secondary text-decoration-none">
                            <i class="bi bi-github me-1"></i> GitHub
                        </a>
                        <?php endif; ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-calendar me-1"></i>
                            Joined <?= date('M j, Y', strtotime($candidate['joined_at'])) ?>
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($proctorImageCount > 0): ?>
                        <a href="/admin/proctor_images.php?user_id=<?= $candidate['id'] ?>" class="btn btn-sm btn-outline-secondary" title="<?= $proctorImageCount ?> proctor images">
                            <i class="bi bi-camera me-1"></i><?= $proctorImageCount ?>
                        </a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#reviewModal<?= $candidate['id'] ?>"
                                data-testid="review-btn-<?= $candidate['id'] ?>">
                            <i class="bi bi-pencil me-1"></i> Review
                        </button>
                        <a href="/admin/users.php?q=<?= urlencode($candidate['email']) ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-person me-1"></i> Profile
                        </a>
                    </div>
                </div>

                <?php if (!empty($candidate['admin_notes'])): ?>
                <div class="mt-2 p-3" style="background: var(--surface-hover); border-radius: var(--radius);">
                    <div class="small text-muted mb-1"><i class="bi bi-sticky me-1"></i> Admin Notes</div>
                    <p class="mb-0 small"><?= nl2br(h($candidate['admin_notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal<?= $candidate['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background: var(--surface); border: 1px solid var(--border);">
                        <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                            <div>
                                <h5 class="modal-title mb-0"><?= h($candidate['name']) ?></h5>
                                <div class="small text-muted"><?= h($candidate['email']) ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                        </div>

                        <div class="modal-body p-0">
                            <!-- Tabs -->
                            <ul class="nav nav-tabs px-3 pt-2" style="border-color: var(--border)">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-status-<?= $candidate['id'] ?>">Status &amp; Notes</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-scores-<?= $candidate['id'] ?>">Score Breakdown</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history-<?= $candidate['id'] ?>">Attempt History</button>
                                </li>
                            </ul>

                            <div class="tab-content p-3">
                                <!-- Tab 1: Status & Notes -->
                                <div class="tab-pane fade show active" id="tab-status-<?= $candidate['id'] ?>">
                                    <form method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="user_id" value="<?= $candidate['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Eligibility Status</label>
                                            <select name="status" class="form-select" data-testid="status-select-<?= $candidate['id'] ?>">
                                                <option value="pending"        <?= $status === 'pending'        ? 'selected' : '' ?>>Pending</option>
                                                <option value="eligible"       <?= $status === 'eligible'       ? 'selected' : '' ?>>Eligible for Hackathon</option>
                                                <option value="to_be_decided"  <?= $status === 'to_be_decided'  ? 'selected' : '' ?>>To be Decided (failed qualifying)</option>
                                                <option value="needs_review"   <?= $status === 'needs_review'   ? 'selected' : '' ?>>Needs Further Review</option>
                                                <option value="rejected"       <?= $status === 'rejected'       ? 'selected' : '' ?>>Rejected</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Admin Notes</label>
                                            <textarea name="notes" class="form-control" rows="4" placeholder="Add notes about this candidate..."><?= h($candidate['admin_notes'] ?? '') ?></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary" data-testid="save-review-<?= $candidate['id'] ?>">Save Changes</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Tab 2: Score Breakdown -->
                                <div class="tab-pane fade" id="tab-scores-<?= $candidate['id'] ?>">
                                    <!-- Composite formula -->
                                    <div class="rounded p-3 mb-3" style="background:var(--bg)">
                                        <div class="small text-muted mb-2 fw-600">Composite Score Formula</div>
                                        <div class="d-flex flex-column gap-1 small">
                                            <div class="d-flex justify-content-between">
                                                <span>Quiz Average × 30%</span>
                                                <strong><?= round($candidate['avg_quiz_score'] ?? 0, 1) ?>% × 0.30 = <?= round(($candidate['avg_quiz_score'] ?? 0) * 0.30, 1) ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Final Exam Average × 70%</span>
                                                <strong><?= round($avgExamScore ?? 0, 1) ?>% × 0.70 = <?= round(($avgExamScore ?? 0) * 0.70, 1) ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between text-success">
                                                <span>Speed Bonus</span>
                                                <strong>+<?= number_format($candidate['speed_bonus'] ?? 0, 2) ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between text-danger">
                                                <span>Attempt Penalty</span>
                                                <strong>−<?= number_format($candidate['attempt_penalty'] ?? 0, 2) ?></strong>
                                            </div>
                                            <hr class="my-1" style="border-color:var(--border)">
                                            <div class="d-flex justify-content-between fw-700">
                                                <span>Composite Score</span>
                                                <span class="<?= ($compositeScore ?? 0) >= 75 ? 'text-success' : 'text-warning' ?>">
                                                    <?= number_format($compositeScore ?? 0, 2) ?>%
                                                    <?= ($compositeScore ?? 0) >= 75 ? '✓ Threshold met' : '✗ Below 75% threshold' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Per-course exam breakdown -->
                                    <div class="small text-muted fw-600 mb-2">Per-Course Final Exam Results</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0" style="font-size:.85rem">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th class="text-center">Completed</th>
                                                    <th class="text-center">Best Score</th>
                                                    <th class="text-center">Attempts</th>
                                                    <th class="text-center">Passed</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($courseExams as $ce): ?>
                                                <tr>
                                                    <td><?= h($ce['title']) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($ce['completed']): ?>
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                        <?php else: ?>
                                                        <i class="bi bi-x-circle text-danger"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $ce['best_score'] !== null ? $ce['best_score'] . '%' : '—' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?= $ce['has_exam'] ? $ce['attempt_count'] : 'N/A' ?>
                                                        <?php if ($ce['attempt_count'] > 1): ?>
                                                        <span class="text-danger small">(+<?= $ce['attempt_count']-1 ?> penalty)</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($ce['best_score'] !== null): ?>
                                                        <span class="<?= $ce['passed'] ? 'text-success' : 'text-danger' ?>">
                                                            <?= $ce['passed'] ? 'Yes' : 'No' ?>
                                                        </span>
                                                        <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Qualifying exam summary -->
                                    <div class="mt-3 rounded p-3" style="background:var(--bg)">
                                        <div class="small fw-600 text-muted mb-2">Qualifying (Final) Exam</div>
                                        <?php if ($qualAttemptCount === 0): ?>
                                        <span class="small text-muted">Not yet attempted</span>
                                        <?php else: ?>
                                        <div class="d-flex gap-3 small">
                                            <div>
                                                <div class="text-muted">Best score</div>
                                                <strong class="<?= $qualPassed ? 'text-success' : 'text-danger' ?>">
                                                    <?= $qualScore ?>%
                                                </strong>
                                            </div>
                                            <div>
                                                <div class="text-muted">Attempts</div>
                                                <strong><?= $qualAttemptCount ?></strong>
                                            </div>
                                            <div>
                                                <div class="text-muted">Result</div>
                                                <strong class="<?= $qualPassed ? 'text-success' : 'text-danger' ?>">
                                                    <?= $qualPassed ? 'Passed' : 'Failed' ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Tab 3: Attempt History -->
                                <div class="tab-pane fade" id="tab-history-<?= $candidate['id'] ?>">
                                    <?php if (empty($qualAttempts)): ?>
                                    <p class="text-muted small">No qualifying exam attempts yet.</p>
                                    <?php else: ?>
                                    <div class="small text-muted fw-600 mb-2">Qualifying Exam Attempts</div>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm" style="font-size:.85rem">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Date</th>
                                                    <th>Score</th>
                                                    <th>Time Taken</th>
                                                    <th>Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($qualAttempts as $ai => $qa): ?>
                                                <tr>
                                                    <td><?= $qualAttemptCount - $ai ?></td>
                                                    <td><?= date('M j Y, H:i', strtotime($qa['completed_at'])) ?></td>
                                                    <td><?= $qa['percentage'] ?>%</td>
                                                    <td>
                                                        <?php
                                                        $t = (int)$qa['time_taken'];
                                                        echo $t ? floor($t/60) . 'm ' . ($t%60) . 's' : '—';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="<?= $qa['passed'] ? 'text-success' : 'text-danger' ?>">
                                                            <?= $qa['passed'] ? 'Passed' : 'Failed' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Per-course exam timeline -->
                                    <div class="small text-muted fw-600 mb-2">Final Exam Attempt History (per course)</div>
                                    <?php foreach ($courses as $c):
                                        $exam = get_final_exam_for_course($c['id']);
                                        if (!$exam) continue;
                                        $allExamAttempts = db()->prepare('SELECT score, passed, started_at, completed_at, time_taken FROM final_exam_attempts WHERE user_id = ? AND exam_id = ? AND completed_at IS NOT NULL ORDER BY completed_at DESC');
                                        $allExamAttempts->execute([$candidate['id'], $exam['id']]);
                                        $examAttempts = $allExamAttempts->fetchAll();
                                        if (empty($examAttempts)) continue;
                                    ?>
                                    <div class="mb-3">
                                        <div class="small fw-600 mb-1"><?= h($c['title']) ?></div>
                                        <table class="table table-sm" style="font-size:.82rem">
                                            <thead>
                                                <tr>
                                                    <th>#</th><th>Date</th><th>Score</th><th>Time</th><th>Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($examAttempts as $ei => $ea): ?>
                                                <tr>
                                                    <td><?= count($examAttempts) - $ei ?></td>
                                                    <td><?= date('M j Y', strtotime($ea['completed_at'])) ?></td>
                                                    <td><?= $ea['score'] ?>%</td>
                                                    <td>
                                                        <?php
                                                        $t = (int)$ea['time_taken'];
                                                        echo $t ? floor($t/60) . 'm ' . ($t%60) . 's' : '—';
                                                        ?>
                                                    </td>
                                                    <td class="<?= $ea['passed'] ? 'text-success' : 'text-danger' ?>">
                                                        <?= $ea['passed'] ? 'Passed' : 'Failed' ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div><!-- /.tab-content -->
                        </div><!-- /.modal-body -->
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($candidates)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">No candidates found</h5>
                    <p class="text-muted">There are no candidates matching this filter.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small">
                Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalCandidates) ?> of <?= $totalCandidates ?> candidates
            </span>
            <?= render_pagination($page, $totalPages, $paginationBase) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
