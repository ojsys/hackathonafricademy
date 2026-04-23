<?php
$pageTitle = 'Proctor Images';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

// List all candidates who have proctor images, or show specific user
if ($userId) {
    $stmt = db()->prepare('SELECT u.id, u.name, u.email FROM users u WHERE u.id = ?');
    $stmt->execute([$userId]);
    $candidate = $stmt->fetch();
    if (!$candidate) { header('Location: /admin/proctor_images.php'); exit; }
    $candidates  = [$candidate];
    $totalPages  = 1;
    $page        = 1;
    $totalCandidates = 1;
    $perPage     = 1;
    $paginationBase  = '';
} else {
    $perPage = 20;
    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $offset  = ($page - 1) * $perPage;

    $countStmt = db()->query('
        SELECT COUNT(DISTINCT u.id)
        FROM users u
        JOIN proctor_images pi ON pi.user_id = u.id
        JOIN qualifying_attempts qa ON qa.id = pi.attempt_id
    ');
    $totalCandidates = (int) $countStmt->fetchColumn();
    $totalPages = (int) ceil($totalCandidates / $perPage);

    $stmt = db()->prepare('
        SELECT DISTINCT u.id, u.name, u.email,
            COUNT(pi.id) AS image_count,
            MAX(pi.captured_at) AS last_capture,
            MAX(qa.percentage) AS best_score,
            MAX(qa.passed) AS passed
        FROM users u
        JOIN proctor_images pi ON pi.user_id = u.id
        JOIN qualifying_attempts qa ON qa.id = pi.attempt_id
        GROUP BY u.id
        ORDER BY last_capture DESC
        LIMIT ' . $perPage . ' OFFSET ' . $offset . '
    ');
    $stmt->execute();
    $candidates = $stmt->fetchAll();
    $paginationBase = '/admin/proctor_images.php?';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-content">

        <div class="d-flex align-items-center gap-3 mb-4">
            <?php if ($userId): ?>
            <a href="/admin/proctor_images.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All Candidates</a>
            <?php endif; ?>
            <h1 class="admin-page-title mb-0"><?= $userId ? 'Proctor Images — ' . h($candidate['name']) : 'Proctor Images' ?></h1>
        </div>

        <?php render_flash(); ?>

        <?php if (!$userId): ?>
        <!-- Candidate list with image counts -->
        <?php if (empty($candidates)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">No proctor images captured yet.</div></div>
        <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Images</th>
                            <th>Best Score</th>
                            <th>Status</th>
                            <th>Last Capture</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-600"><?= h($c['name']) ?></div>
                                <div class="small text-muted"><?= h($c['email']) ?></div>
                            </td>
                            <td><span class="fw-600"><?= $c['image_count'] ?></span> <span class="text-muted small">photos</span></td>
                            <td><?= $c['best_score'] !== null ? $c['best_score'] . '%' : '—' ?></td>
                            <td>
                                <?php if ($c['passed']): ?>
                                <span class="badge-chip chip-published">Passed</span>
                                <?php else: ?>
                                <span class="badge-chip chip-draft">Not Passed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= date('M j, Y H:i', strtotime($c['last_capture'])) ?></td>
                            <td>
                                <a href="/admin/proctor_images.php?user_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-camera me-1"></i>View Images
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small">
                Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalCandidates) ?> of <?= $totalCandidates ?> candidates
            </span>
            <?= render_pagination($page, $totalPages, $paginationBase) ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php else: ?>
        <!-- Individual candidate images grouped by attempt -->
        <?php
        $stmt = db()->prepare('
            SELECT qa.id AS attempt_id, qa.percentage, qa.passed, qa.started_at, qa.completed_at,
                   ps.camera_granted,
                   GROUP_CONCAT(pi.id) AS image_ids,
                   COUNT(pi.id) AS image_count
            FROM qualifying_attempts qa
            LEFT JOIN proctor_sessions ps ON ps.attempt_id = qa.id
            LEFT JOIN proctor_images pi ON pi.attempt_id = qa.id
            WHERE qa.user_id = ? AND qa.completed_at IS NOT NULL
            GROUP BY qa.id
            ORDER BY qa.started_at DESC
        ');
        $stmt->execute([$userId]);
        $attempts = $stmt->fetchAll();
        ?>

        <?php if (empty($attempts)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">No completed exam attempts found.</div></div>
        <?php endif; ?>

        <?php foreach ($attempts as $att): ?>
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <strong>Attempt on <?= date('M j, Y \a\t H:i', strtotime($att['started_at'])) ?></strong>
                    <span class="ms-2 badge-chip <?= $att['passed'] ? 'chip-published' : 'chip-draft' ?>"><?= $att['passed'] ? 'Passed' : 'Failed' ?> — <?= $att['percentage'] ?>%</span>
                </div>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <i class="bi bi-camera<?= $att['camera_granted'] ? '-fill text-success' : '-video-off text-danger' ?>"></i>
                    <?= $att['camera_granted'] ? 'Camera granted' : 'Camera denied' ?>
                    <span class="ms-2"><?= $att['image_count'] ?> image<?= $att['image_count'] != 1 ? 's' : '' ?></span>
                </div>
            </div>
            <?php
            $imgStmt = db()->prepare('SELECT * FROM proctor_images WHERE attempt_id = ? ORDER BY captured_at');
            $imgStmt->execute([$att['attempt_id']]);
            $images = $imgStmt->fetchAll();
            ?>
            <?php if (empty($images)): ?>
            <div class="card-body text-muted small py-3">
                <?= $att['camera_granted'] ? 'No images captured during this attempt.' : 'Candidate did not grant camera access.' ?>
            </div>
            <?php else: ?>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($images as $img): ?>
                    <div style="position:relative">
                        <img src="/public/<?= h($img['image_path']) ?>"
                             alt="Capture at <?= date('H:i:s', strtotime($img['captured_at'])) ?>"
                             style="width:160px;height:120px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);cursor:pointer"
                             onclick="openLightbox(this.src, '<?= date('M j Y H:i:s', strtotime($img['captured_at'])) ?>')"
                             title="Captured at <?= date('H:i:s', strtotime($img['captured_at'])) ?>">
                        <div style="font-size:.65rem;color:var(--text-muted);text-align:center;margin-top:2px"><?= date('H:i:s', strtotime($img['captured_at'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:2000;align-items:center;justify-content:center;flex-direction:column;gap:1rem"
     onclick="closeLightbox()">
    <img id="lightbox-img" src="" style="max-width:90vw;max-height:80vh;border-radius:var(--radius);box-shadow:0 8px 40px rgba(0,0,0,.6)">
    <div id="lightbox-caption" style="color:#fff;font-size:.85rem;opacity:.7"></div>
</div>

<script>
function openLightbox(src, caption) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox-caption').textContent = caption;
    document.getElementById('lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
