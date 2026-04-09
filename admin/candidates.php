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
    
    if ($userId && in_array($status, ['pending', 'eligible', 'needs_review', 'rejected'])) {
        // Update review status — works for both SQLite and MySQL
        $existing = db()->prepare('SELECT id FROM candidate_reviews WHERE user_id = ?');
        $existing->execute([$userId]);
        
        if ($existing->fetch()) {
            $stmt = db()->prepare('UPDATE candidate_reviews SET eligibility_status = ?, admin_notes = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
            $stmt->execute([$status, $notes, current_user()['id'], $userId]);
        } else {
            $stmt = db()->prepare('INSERT INTO candidate_reviews (user_id, eligibility_status, admin_notes, reviewed_by, reviewed_at, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
            $stmt->execute([$userId, $status, $notes, current_user()['id']]);
        }
        
        set_flash('success', 'Candidate status updated successfully.');
    }
    header('Location: /admin/candidates.php');
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? 'all';
$candidates = get_candidates_for_review($statusFilter);

// Stats
$stats = [
    'total' => db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn(),
    'eligible' => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "eligible"')->fetchColumn(),
    'pending' => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "pending"')->fetchColumn(),
    'needs_review' => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "needs_review"')->fetchColumn(),
    'rejected' => db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "rejected"')->fetchColumn(),
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
                <!-- Pipeline Actions -->
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
                                <button class="btn btn-sm btn-primary w-100" onclick="return confirm('This will replace the current shortlist with the top <?= get_setting('shortlist_limit','100') ?> eligible candidates by composite score. Continue?')">
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
                                <button class="btn btn-sm btn-success w-100" onclick="return confirm('Send bootcamp invitation emails to all shortlisted candidates who have not yet been invited?')">
                                    <i class="bi bi-envelope-check me-1"></i> Send Invitations
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="/actions/admin/shortlist_candidates.php" method="POST" class="px-3 pb-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="notify_not_selected">
                                <button class="btn btn-sm btn-outline-secondary w-100" onclick="return confirm('Send appreciation emails to all non-shortlisted candidates who completed the courses?')">
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
            <div class="col-6 col-md-3">
                <a href="?status=all" class="card text-decoration-none <?= $statusFilter === 'all' ? 'border-primary' : '' ?>" data-testid="filter-all">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1"><?= $stats['total'] ?></div>
                        <div class="text-muted small">All Candidates</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="?status=eligible" class="card text-decoration-none <?= $statusFilter === 'eligible' ? 'border-primary' : '' ?>" data-testid="filter-eligible">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1 text-success"><?= $stats['eligible'] ?></div>
                        <div class="text-muted small">Eligible</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="?status=needs_review" class="card text-decoration-none <?= $statusFilter === 'needs_review' ? 'border-primary' : '' ?>" data-testid="filter-review">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1 text-warning"><?= $stats['needs_review'] ?></div>
                        <div class="text-muted small">Needs Review</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="?status=pending" class="card text-decoration-none <?= $statusFilter === 'pending' ? 'border-primary' : '' ?>" data-testid="filter-pending">
                    <div class="card-body text-center py-3">
                        <div class="fw-700 h4 mb-1"><?= $stats['pending'] ?></div>
                        <div class="text-muted small">Pending</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Candidates List -->
        <div class="d-flex flex-column gap-3">
            <?php foreach ($candidates as $candidate): 
                $eligible = is_eligible($candidate['id']);
                $courses = get_all_published_courses();
                $coursesCompleted = 0;
                foreach ($courses as $c) {
                    if (is_course_complete($candidate['id'], $c['id'])) $coursesCompleted++;
                }
                $status = $candidate['eligibility_status'] ?? 'pending';
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
                        <?php
                        $statusIcons = [
                            'eligible' => 'bi-check-circle-fill',
                            'pending' => 'bi-hourglass-split',
                            'needs_review' => 'bi-eye-fill',
                            'rejected' => 'bi-x-circle-fill'
                        ];
                        $statusLabels = [
                            'eligible' => 'Eligible',
                            'pending' => 'Pending',
                            'needs_review' => 'Needs Review',
                            'rejected' => 'Rejected'
                        ];
                        ?>
                        <i class="bi <?= $statusIcons[$status] ?? 'bi-question-circle' ?>"></i>
                        <?= $statusLabels[$status] ?? 'Unknown' ?>
                    </div>
                </div>

                <div class="candidate-stats">
                    <div class="candidate-stat">
                        <div class="candidate-stat-value"><?= $coursesCompleted ?>/<?= count($courses) ?></div>
                        <div class="candidate-stat-label">Courses Done</div>
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
                        <div class="candidate-stat-value"><?= round($candidate['total_score'] ?? 0) ?>%</div>
                        <div class="candidate-stat-label">Total Score</div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
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
                            <?= h($candidate['years_experience']) ?> experience
                        </span>
                        <?php endif; ?>
                        <?php if ($candidate['github_url']): ?>
                        <a href="<?= h($candidate['github_url']) ?>" target="_blank" class="badge bg-secondary text-decoration-none">
                            <i class="bi bi-github me-1"></i> GitHub
                        </a>
                        <?php endif; ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-calendar me-1"></i>
                            Joined <?= date('M j, Y', strtotime($candidate['created_at'])) ?>
                        </span>
                    </div>

                    <div class="d-flex gap-2">
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
                <div class="mt-3 p-3" style="background: var(--surface-hover); border-radius: var(--radius);">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-sticky me-1"></i> Admin Notes
                    </div>
                    <p class="mb-0 small"><?= nl2br(h($candidate['admin_notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal<?= $candidate['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content" style="background: var(--surface); border: 1px solid var(--border);">
                        <div class="modal-header border-bottom" style="border-color: var(--border) !important;">
                            <h5 class="modal-title">Review: <?= h($candidate['name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                        </div>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="user_id" value="<?= $candidate['id'] ?>">
                            
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" data-testid="status-select-<?= $candidate['id'] ?>">
                                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="eligible" <?= $status === 'eligible' ? 'selected' : '' ?>>Eligible for Hackathon</option>
                                        <option value="needs_review" <?= $status === 'needs_review' ? 'selected' : '' ?>>Needs Further Review</option>
                                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Admin Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Add notes about this candidate..."><?= h($candidate['admin_notes'] ?? '') ?></textarea>
                                </div>

                                <div class="p-3 rounded" style="background: var(--bg);">
                                    <div class="small text-muted mb-2">Quick Stats</div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Courses Completed:</span>
                                        <strong><?= $coursesCompleted ?>/<?= count($courses) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Quiz Average:</span>
                                        <strong><?= round($candidate['avg_quiz_score'] ?? 0) ?>%</strong>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span>Auto-Eligible:</span>
                                        <strong class="<?= $eligible ? 'text-success' : 'text-danger' ?>">
                                            <?= $eligible ? 'Yes' : 'No' ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer border-top" style="border-color: var(--border) !important;">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" data-testid="save-review-<?= $candidate['id'] ?>">Save Changes</button>
                            </div>
                        </form>
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
