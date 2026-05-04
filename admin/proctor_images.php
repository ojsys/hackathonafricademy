<?php
$pageTitle = 'Proctor Images';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if ($userId) {
    $stmt = db()->prepare('SELECT u.id, u.name, u.email FROM users u WHERE u.id = ?');
    $stmt->execute([$userId]);
    $candidate = $stmt->fetch();
    if (!$candidate) { header('Location: /admin/proctor_images.php'); exit; }
    $candidates      = [$candidate];
    $totalPages      = 1;
    $page            = 1;
    $totalCandidates = 1;
    $perPage         = 1;
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

        // Build a flat JS-accessible images array per attempt for the gallery modal
        $allGalleries = [];
        foreach ($attempts as $att) {
            $imgStmt = db()->prepare('SELECT * FROM proctor_images WHERE attempt_id = ? ORDER BY captured_at');
            $imgStmt->execute([$att['attempt_id']]);
            $imgs = $imgStmt->fetchAll();
            $galleryImages = [];
            foreach ($imgs as $img) {
                $galleryImages[] = [
                    'src'       => '/public/' . $img['image_path'],
                    'timestamp' => date('M j Y, H:i:s', strtotime($img['captured_at'])),
                ];
            }
            $allGalleries[$att['attempt_id']] = $galleryImages;
        }
        ?>

        <?php if (empty($attempts)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">No completed exam attempts found.</div></div>
        <?php endif; ?>

        <?php foreach ($attempts as $att):
            $images = $allGalleries[$att['attempt_id']] ?? [];
        ?>
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <strong>Attempt on <?= date('M j, Y \a\t H:i', strtotime($att['started_at'])) ?></strong>
                    <span class="ms-2 badge-chip <?= $att['passed'] ? 'chip-published' : 'chip-draft' ?>">
                        <?= $att['passed'] ? 'Passed' : 'Failed' ?> — <?= $att['percentage'] ?>%
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <i class="bi bi-camera<?= $att['camera_granted'] ? '-fill text-success' : '-video-off text-danger' ?>"></i>
                    <?= $att['camera_granted'] ? 'Camera granted' : 'Camera denied' ?>
                    <span class="ms-2"><?= $att['image_count'] ?> image<?= $att['image_count'] != 1 ? 's' : '' ?></span>
                </div>
            </div>

            <?php if (empty($images)): ?>
            <div class="card-body text-muted small py-3">
                <?= $att['camera_granted'] ? 'No images captured during this attempt.' : 'Candidate did not grant camera access.' ?>
            </div>
            <?php else: ?>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($images as $idx => $img): ?>
                    <div style="position:relative;cursor:pointer" onclick="openGallery(<?= (int)$att['attempt_id'] ?>, <?= $idx ?>)">
                        <img src="<?= h($img['src']) ?>"
                             alt="Capture at <?= h($img['timestamp']) ?>"
                             style="width:160px;height:120px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);transition:opacity .15s"
                             onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                        <div style="font-size:.65rem;color:var(--text-muted);text-align:center;margin-top:2px">
                            <?= date('H:i:s', strtotime($img['timestamp'])) ?>
                        </div>
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

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background:var(--surface);border:1px solid var(--border);">
            <div class="modal-header border-bottom" style="border-color:var(--border)!important">
                <span id="gallery-counter" class="text-muted small"></span>
                <span id="gallery-timestamp" class="small text-muted ms-3"></span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-0" style="background:#000;position:relative;min-height:400px;display:flex;align-items:center;justify-content:center">
                <!-- Prev button -->
                <button id="gallery-prev" onclick="galleryNav(-1)"
                    style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,.5);border:none;color:#fff;border-radius:50%;width:44px;height:44px;font-size:1.4rem;display:flex;align-items:center;justify-content:center;cursor:pointer">
                    &#8249;
                </button>
                <img id="gallery-img" src="" alt=""
                     style="max-width:100%;max-height:70vh;object-fit:contain;display:block">
                <!-- Next button -->
                <button id="gallery-next" onclick="galleryNav(1)"
                    style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);z-index:10;background:rgba(0,0,0,.5);border:none;color:#fff;border-radius:50%;width:44px;height:44px;font-size:1.4rem;display:flex;align-items:center;justify-content:center;cursor:pointer">
                    &#8250;
                </button>
            </div>
            <!-- Thumbnail strip -->
            <div class="modal-footer border-top p-2" style="border-color:var(--border)!important;overflow-x:auto;flex-wrap:nowrap;justify-content:flex-start;gap:.5rem">
                <div id="gallery-thumbs" class="d-flex gap-2" style="flex-wrap:nowrap"></div>
            </div>
        </div>
    </div>
</div>

<script>
const galleries = <?= json_encode($allGalleries ?? []) ?>;
let currentGalleryId  = null;
let currentImageIndex = 0;
let galleryModal      = null;

function openGallery(attemptId, index) {
    currentGalleryId  = attemptId;
    currentImageIndex = index;
    if (!galleryModal) galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
    renderGallery();
    galleryModal.show();
}

function renderGallery() {
    const images = galleries[currentGalleryId] || [];
    if (!images.length) return;
    const img = images[currentImageIndex];

    document.getElementById('gallery-img').src       = img.src;
    document.getElementById('gallery-timestamp').textContent = img.timestamp;
    document.getElementById('gallery-counter').textContent   = (currentImageIndex + 1) + ' of ' + images.length;

    document.getElementById('gallery-prev').style.display = currentImageIndex > 0                ? 'flex' : 'none';
    document.getElementById('gallery-next').style.display = currentImageIndex < images.length - 1 ? 'flex' : 'none';

    // Rebuild thumbnails
    const thumbsEl = document.getElementById('gallery-thumbs');
    thumbsEl.innerHTML = '';
    images.forEach((im, i) => {
        const thumb = document.createElement('img');
        thumb.src = im.src;
        thumb.title = im.timestamp;
        thumb.style.cssText = 'width:64px;height:48px;object-fit:cover;border-radius:4px;cursor:pointer;flex-shrink:0;border:2px solid ' + (i === currentImageIndex ? 'var(--primary)' : 'transparent') + ';opacity:' + (i === currentImageIndex ? '1' : '.55') + ';transition:opacity .15s';
        thumb.onclick = () => { currentImageIndex = i; renderGallery(); };
        thumbsEl.appendChild(thumb);
    });

    // Scroll active thumbnail into view
    const activeTh = thumbsEl.children[currentImageIndex];
    if (activeTh) activeTh.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
}

function galleryNav(dir) {
    const images = galleries[currentGalleryId] || [];
    currentImageIndex = Math.max(0, Math.min(images.length - 1, currentImageIndex + dir));
    renderGallery();
}

document.addEventListener('keydown', e => {
    if (!galleryModal?._isShown) return;
    if (e.key === 'ArrowLeft')  galleryNav(-1);
    if (e.key === 'ArrowRight') galleryNav(1);
    if (e.key === 'Escape')     galleryModal.hide();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
