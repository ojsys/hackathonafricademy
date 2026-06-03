<?php
$pageTitle = 'Interview Reviews';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$sessionId = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);

/* ───────────────────────── Detail view ───────────────────────── */
if ($sessionId) {
    $session = get_interview_session($sessionId);
    if (!$session) { header('Location: /admin/interview_reviews.php'); exit; }

    $cand = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $cand->execute([$session['user_id']]);
    $candidate = $cand->fetch();

    $orderedIds = json_decode($session['exercise_ids_json'] ?? '[]', true) ?: [];
    $exercises  = get_interview_exercises_in_order(array_map('intval', $orderedIds));
    $answers    = get_interview_answers($sessionId);
    $events     = get_interview_events($sessionId);
    $images     = get_interview_proctor_images($sessionId);

    // Saved "form → database" entries for this session, grouped by exercise.
    $sandboxByExercise = [];
    $sbStmt = db()->prepare('SELECT exercise_id, payload_json, created_at FROM interview_sandbox_entries WHERE session_id = ? ORDER BY id');
    $sbStmt->execute([$sessionId]);
    foreach ($sbStmt->fetchAll() as $r) {
        $sandboxByExercise[(int)$r['exercise_id']][] = json_decode($r['payload_json'], true) ?: [];
    }

    // Build the JS payload: full test cases (sample + hidden) + candidate code.
    $jsTasks = [];
    foreach ($exercises as $i => $ex) {
        $tc  = json_decode($ex['test_cases_json'] ?? '{}', true) ?: [];
        $ans = $answers[(int)$ex['id']] ?? null;
        $jsTasks[] = [
            'exercise_id'   => (int)$ex['id'],
            'kind'          => $ex['kind'],
            'category'      => $ex['category'],
            'exercise_type' => $ex['exercise_type'] ?? 'javascript',
            'title'         => $ex['title'],
            'instructions'  => $ex['instructions'],
            'entry'         => $tc['entry'] ?? '',
            'cases'         => $tc['cases'] ?? [],
            'points'        => (int)$ex['points'],
            'code'          => $ans['submitted_code'] ?? $ex['starter_code'],
        ];
    }

    require_once __DIR__ . '/../includes/header.php';
    ?>
    <link rel="stylesheet" href="/public/css/interview.css">
    <div class="admin-layout">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>
        <div class="admin-content">
            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                <a href="/admin/interview_reviews.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All Reviews</a>
                <h1 class="admin-page-title mb-0">Interview — <?= h($candidate['name']) ?></h1>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <?php if (!empty($session['is_test'])): ?>
                    <span class="badge bg-secondary"><i class="bi bi-flask me-1"></i>TEST RUN</span>
                    <?php endif; ?>
                    <span class="badge-chip <?= $session['status'] === 'reviewed' ? 'chip-published' : 'chip-draft' ?>">
                        <?= $session['status'] === 'reviewed' ? 'Reviewed — ' . ucfirst($session['review_decision']) : 'Pending review' ?>
                    </span>
                    <form method="POST" action="/actions/admin/reset_interview.php" class="m-0"
                          onsubmit="return confirm('Delete this interview for <?= h(addslashes($candidate['name'])) ?>? Their answers, proctor snapshots and event log will be permanently removed and they can start the interview over. This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="session_id" value="<?= (int)$sessionId ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>Delete &amp; Reset
                        </button>
                    </form>
                </div>
            </div>
            <?php render_flash(); ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Candidate</div><div class="fw-600"><?= h($candidate['email']) ?></div></div></div>
                <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Submitted</div><div class="fw-600"><?= $session['submitted_at'] ? date('M j, Y H:i', strtotime($session['submitted_at'])) : '—' ?></div></div></div>
                <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Sample auto-score</div><div class="fw-600"><?= (int)$session['auto_score'] ?>% <span class="text-muted small">(advisory)</span></div></div></div>
                <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Integrity flags</div><div class="fw-600 <?= count(array_filter($events, fn($e) => $e['event_type'] !== 'camera_granted')) ? 'text-danger' : '' ?>"><?= count(array_filter($events, fn($e) => $e['event_type'] !== 'camera_granted')) ?></div></div></div>
            </div>

            <!-- Proctoring evidence -->
            <div class="card mb-4">
                <div class="card-header fw-700"><i class="bi bi-shield-lock me-1"></i>Proctoring</div>
                <div class="card-body">
                    <h6 class="small text-muted text-uppercase mb-2">Camera snapshots (<?= count($images) ?>)</h6>
                    <?php if (empty($images)): ?>
                    <p class="text-muted small">No snapshots captured (camera denied or unavailable).</p>
                    <?php else: ?>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="iv-snapshots">
                        <?php foreach ($images as $idx => $img): ?>
                        <img src="/public/<?= h($img['image_path']) ?>" class="iv-snap" data-index="<?= $idx ?>"
                             title="<?= date('H:i:s', strtotime($img['captured_at'])) ?>"
                             style="width:120px;height:90px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);cursor:pointer">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <h6 class="small text-muted text-uppercase mb-2">Integrity event log</h6>
                    <?php if (empty($events)): ?>
                    <p class="text-muted small">No events recorded.</p>
                    <?php else: ?>
                    <div class="table-responsive" style="max-height:240px;overflow:auto">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <?php foreach ($events as $e):
                                $danger = !in_array($e['event_type'], ['camera_granted']); ?>
                            <tr>
                                <td class="text-muted small" style="white-space:nowrap"><?= date('H:i:s', strtotime($e['created_at'])) ?></td>
                                <td><span class="badge <?= $danger ? 'bg-danger-subtle text-danger-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>"><?= h($e['event_type']) ?></span></td>
                                <td class="small text-muted"><?= h($e['detail']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Scoring form -->
            <form method="POST" action="/actions/admin/save_interview_review.php" id="review-form">
                <?= csrf_field() ?>
                <input type="hidden" name="session_id" value="<?= (int)$sessionId ?>">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="fw-700 mb-0">Submissions (<?= count($jsTasks) ?>)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="run-all-btn">
                        <i class="bi bi-play-circle me-1"></i>Run all hidden tests
                    </button>
                </div>

                <?php foreach ($jsTasks as $i => $t):
                    $ans = $answers[$t['exercise_id']] ?? null; ?>
                <?php
                    $kindBadge = $t['kind'] === 'coding' ? ['bg-info-subtle text-info-emphasis', 'Coding']
                               : ($t['kind'] === 'debugging' ? ['bg-danger-subtle text-danger-emphasis', 'Debug']
                               : ['bg-success-subtle text-success-emphasis', 'Applied']);
                    $entries = $sandboxByExercise[$t['exercise_id']] ?? [];
                ?>
                <div class="card mb-3" data-task="<?= $i ?>">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge <?= $kindBadge[0] ?>"><?= $kindBadge[1] ?></span>
                            <strong class="ms-2"><?= h($t['title']) ?></strong>
                        </div>
                        <div class="small text-muted" id="auto-result-<?= $i ?>"><?= $t['kind'] === 'project' ? 'Click to preview' : 'Not run' ?></div>
                    </div>
                    <div class="card-body">
                        <pre class="iv-code-view"><code><?= h($t['code']) ?></code></pre>
                        <?php if ($t['kind'] === 'project'): ?>
                        <div class="iv-preview-label mt-2">Rendered preview</div>
                        <iframe class="iv-preview" id="prev-<?= $i ?>" title="preview"></iframe>
                        <?php if ($t['category'] === 'form_db'): ?>
                        <div class="iv-preview-label mt-2">Saved to database (<?= count($entries) ?> row<?= count($entries) === 1 ? '' : 's' ?>)</div>
                        <?php if ($entries): ?>
                        <div class="iv-code-view" style="max-height:160px"><?php foreach ($entries as $row): ?><?= h(json_encode($row, JSON_UNESCAPED_SLASHES)) ?>
<?php endforeach; ?></div>
                        <?php else: ?>
                        <div class="text-muted small">No rows were saved by the candidate.</div>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php endif; ?>
                        <div class="row g-2 mt-2 align-items-end">
                            <div class="col-auto">
                                <label class="small text-muted d-block">Score (0–<?= $t['points'] ?>)</label>
                                <input type="number" min="0" max="<?= $t['points'] ?>" class="form-control form-control-sm" style="width:110px"
                                       name="score_<?= $t['exercise_id'] ?>" id="score-<?= $i ?>"
                                       value="<?= $ans && $ans['admin_score'] !== null ? (int)$ans['admin_score'] : '' ?>">
                            </div>
                            <div class="col">
                                <label class="small text-muted d-block">Feedback (optional)</label>
                                <input type="text" class="form-control form-control-sm" name="feedback_<?= $t['exercise_id'] ?>"
                                       value="<?= h($ans['admin_feedback'] ?? '') ?>" placeholder="Notes on this submission…">
                            </div>
                            <input type="hidden" name="passed_<?= $t['exercise_id'] ?>" id="passed-<?= $i ?>" value="<?= $ans['admin_passed'] ?? '' ?>">
                            <input type="hidden" name="total_<?= $t['exercise_id'] ?>" id="total-<?= $i ?>" value="<?= $ans['admin_total'] ?? '' ?>">
                        </div>
                        <div class="iv-results mt-2 px-0" id="result-detail-<?= $i ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="fw-700 mb-3">Selection decision</h5>
                        <div class="d-flex gap-3 mb-3 flex-wrap">
                            <?php foreach (['selected' => 'Select candidate', 'rejected' => 'Do not select', 'pending' => 'Keep pending'] as $val => $lbl): ?>
                            <label class="iv-decision-opt">
                                <input type="radio" name="decision" value="<?= $val ?>" <?= ($session['review_decision'] ?: 'pending') === $val ? 'checked' : '' ?>>
                                <?= $lbl ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="small text-muted d-block mb-1">Reviewer notes (shown to the candidate)</label>
                        <textarea name="reviewer_notes" class="form-control" rows="3" placeholder="Overall feedback…"><?= h($session['reviewer_notes'] ?? '') ?></textarea>
                        <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check2-circle me-1"></i>Save Review</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Snapshot lightbox -->
    <div class="iv-lightbox" id="iv-lightbox" aria-hidden="true">
        <button type="button" class="iv-lb-close" data-lb-close aria-label="Close">&times;</button>
        <button type="button" class="iv-lb-nav iv-lb-prev" data-lb-prev aria-label="Previous">&#8249;</button>
        <div class="iv-lb-stage" id="iv-lb-stage">
            <img class="iv-lb-img" id="iv-lb-img" alt="Camera snapshot">
            <div class="iv-lb-caption" id="iv-lb-caption"></div>
        </div>
        <button type="button" class="iv-lb-nav iv-lb-next" data-lb-next aria-label="Next">&#8250;</button>
    </div>

    <style>
    .iv-code-view { background:#0D1117; color:#e0e0e0; border:1px solid var(--border); border-radius:var(--radius); padding:1rem; font-family:var(--font-mono); font-size:.82rem; max-height:320px; overflow:auto; white-space:pre; }
    .iv-decision-opt { display:flex; align-items:center; gap:.4rem; padding:.5rem .9rem; border:1px solid var(--border); border-radius:var(--radius); cursor:pointer; }
    .iv-lightbox { position:fixed; inset:0; z-index:1080; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,.85); padding:1rem; touch-action:pan-y; }
    .iv-lightbox.open { display:flex; }
    .iv-lb-stage { max-width:90vw; max-height:88vh; display:flex; flex-direction:column; align-items:center; gap:.6rem; user-select:none; }
    .iv-lb-img { max-width:90vw; max-height:80vh; object-fit:contain; border-radius:var(--radius); box-shadow:0 10px 40px rgba(0,0,0,.5); pointer-events:none; }
    .iv-lb-caption { color:#fff; font-size:.85rem; opacity:.85; }
    .iv-lb-close { position:absolute; top:1rem; right:1.25rem; background:none; border:none; color:#fff; font-size:2.2rem; line-height:1; cursor:pointer; opacity:.8; }
    .iv-lb-close:hover { opacity:1; }
    .iv-lb-nav { background:rgba(255,255,255,.12); border:none; color:#fff; font-size:2.4rem; line-height:1; width:54px; height:54px; border-radius:50%; cursor:pointer; flex:0 0 auto; transition:background .15s; }
    .iv-lb-nav:hover { background:rgba(255,255,255,.28); }
    .iv-lb-prev { position:absolute; left:1rem; }
    .iv-lb-next { position:absolute; right:1rem; }
    @media (max-width:640px) { .iv-lb-nav { width:44px; height:44px; font-size:2rem; } }
    </style>

    <script src="/public/js/interview_harness.js"></script>
    <script src="/public/js/interview_preview.js"></script>
    <script>
    (function () {
        var TASKS = <?= json_encode($jsTasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var CSRF  = <?= json_encode(csrf_token()) ?>;

        // ── Camera snapshot lightbox (swipable, no new tab) ─────────────
        var SNAPS = <?= json_encode(array_map(function ($img) {
            return ['src' => '/public/' . $img['image_path'], 'time' => date('H:i:s', strtotime($img['captured_at']))];
        }, $images), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        (function initLightbox() {
            if (!SNAPS.length) return;
            var box = document.getElementById('iv-lightbox');
            var imgEl = document.getElementById('iv-lb-img');
            var capEl = document.getElementById('iv-lb-caption');
            var stage = document.getElementById('iv-lb-stage');
            var cur = 0;

            function render() {
                imgEl.src = SNAPS[cur].src;
                capEl.textContent = 'Snapshot ' + (cur + 1) + ' of ' + SNAPS.length + ' · ' + SNAPS[cur].time;
            }
            function open(i) { cur = i; render(); box.classList.add('open'); box.setAttribute('aria-hidden', 'false'); }
            function close() { box.classList.remove('open'); box.setAttribute('aria-hidden', 'true'); }
            function step(d) { cur = (cur + d + SNAPS.length) % SNAPS.length; render(); }

            document.querySelectorAll('#iv-snapshots .iv-snap').forEach(function (el) {
                el.addEventListener('click', function () { open(parseInt(el.dataset.index, 10)); });
            });
            box.querySelector('[data-lb-close]').addEventListener('click', close);
            box.querySelector('[data-lb-prev]').addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
            box.querySelector('[data-lb-next]').addEventListener('click', function (e) { e.stopPropagation(); step(1); });
            // Click the backdrop (but not the image/controls) to close.
            box.addEventListener('click', function (e) { if (e.target === box || e.target === stage) close(); });
            document.addEventListener('keydown', function (e) {
                if (!box.classList.contains('open')) return;
                if (e.key === 'Escape') close();
                else if (e.key === 'ArrowLeft') step(-1);
                else if (e.key === 'ArrowRight') step(1);
            });
            // Touch swipe — navigate within the modal without opening a new image.
            var sx = 0, sy = 0;
            box.addEventListener('touchstart', function (e) { sx = e.changedTouches[0].clientX; sy = e.changedTouches[0].clientY; }, { passive: true });
            box.addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
                if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) step(dx < 0 ? 1 : -1);
            }, { passive: true });
        })();

        function esc(s){return String(s==null?'':s).replace(/[&<>]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];});}

        function reqList(t){ return (t.instructions||'').split('\n').map(function(l){return l.trim();})
            .filter(function(l){return /^\d+\./.test(l);}).map(function(l){return l.replace(/^\d+\.\s*/,'');}); }

        // Applied tasks: render the candidate's work + a requirement checklist.
        function runProject(i) {
            var t = TASKS[i];
            var summ = document.getElementById('auto-result-' + i);
            var detail = document.getElementById('result-detail-' + i);
            var iframe = document.getElementById('prev-' + i);
            try {
                var html = window.interviewBuildPreview(t.code, t.exercise_type, { token: CSRF, exerciseId: t.exercise_id });
                iframe.src = URL.createObjectURL(new Blob([html], { type: 'text/html' }));
            } catch (e) {}
            var reqs = reqList(t);
            var results = window.interviewCheckRequirements(t.code, reqs);
            var met = results.filter(function (r){return r.met;}).length;
            summ.innerHTML = '<span class="fw-700 ' + (met===reqs.length?'text-success':'text-muted') + '">' + met + ' / ' + reqs.length + ' reqs</span>';
            document.getElementById('passed-' + i).value = met;
            document.getElementById('total-' + i).value = reqs.length;
            var scoreEl = document.getElementById('score-' + i);
            if (!scoreEl.value && reqs.length) scoreEl.value = Math.round(met / reqs.length * t.points);
            detail.innerHTML = results.map(function (r) {
                return '<div class="iv-res-row ' + (r.met?'pass':'fail') + '"><i class="bi bi-' + (r.met?'check-circle-fill text-success':'circle text-muted') + ' me-1"></i>' + esc(r.text) + '</div>';
            }).join('') + '<div class="iv-res-info mt-1"><i class="bi bi-info-circle me-1"></i>Checklist is a guide — score the live preview and the saved data above.</div>';
            return Promise.resolve();
        }

        function runTask(i) {
            var t = TASKS[i];
            if (t.kind === 'project') return runProject(i);
            var summ = document.getElementById('auto-result-' + i);
            var detail = document.getElementById('result-detail-' + i);
            if (!t.cases.length) { summ.textContent = 'No tests'; return Promise.resolve(); }
            summ.textContent = 'Running…';
            return window.runInterviewTests(t.code, t.entry, t.cases).then(function (results) {
                var passed = results.filter(function (r){return r.pass;}).length;
                var total = t.cases.length;
                summ.innerHTML = '<span class="fw-700 ' + (passed===total?'text-success':'text-danger') + '">' + passed + ' / ' + total + ' tests</span>';
                document.getElementById('passed-' + i).value = passed;
                document.getElementById('total-' + i).value = total;
                // Suggest a score proportional to tests passed (admin can override).
                var scoreEl = document.getElementById('score-' + i);
                if (!scoreEl.value) scoreEl.value = Math.round(passed / total * t.points);
                detail.innerHTML = results.map(function (r) {
                    if (r.fatal) return '<div class="iv-res-row fail">Error: ' + esc(r.fatal) + '</div>';
                    return '<div class="iv-res-row ' + (r.pass?'pass':'fail') + '">' +
                        '<i class="bi bi-' + (r.pass?'check':'x') + '-lg me-1"></i>' +
                        '<code>' + esc(t.entry) + '(' + esc(JSON.stringify(r.args).slice(1,-1)) + ')</code> → exp <code>' + esc(JSON.stringify(r.expected)) + '</code>' +
                        (r.pass ? '' : (r.error ? ', threw <code>' + esc(r.error) + '</code>' : ', got <code>' + esc(JSON.stringify(r.got)) + '</code>')) +
                        '</div>';
                }).join('');
            });
        }
        document.querySelectorAll('[data-task]').forEach(function (card) {
            var i = parseInt(card.dataset.task, 10);
            card.querySelector('.card-header').style.cursor = 'pointer';
            card.querySelector('.card-header').addEventListener('click', function () { runTask(i); });
        });
        document.getElementById('run-all-btn').addEventListener('click', function () {
            var chain = Promise.resolve();
            TASKS.forEach(function (_, i) { chain = chain.then(function () { return runTask(i); }); });
        });
    })();
    </script>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

/* ───────────────────────── List / queue ───────────────────────── */
// Real candidate submissions (test runs are excluded — listed separately below).
$rows = db()->query("
    SELECT s.*, u.name, u.email,
        (SELECT COUNT(*) FROM interview_events e WHERE e.session_id = s.id AND e.event_type != 'camera_granted') AS flags,
        (SELECT COUNT(*) FROM interview_proctor_images p WHERE p.session_id = s.id) AS images
    FROM interview_sessions s
    JOIN users u ON u.id = s.user_id
    WHERE s.status IN ('submitted','reviewed') AND s.is_test = 0
    ORDER BY (s.status = 'submitted') DESC, s.submitted_at DESC
")->fetchAll();

// Admin test runs (any status) — separate so they never look like candidates.
$testRows = db()->query("
    SELECT s.*, u.name, u.email
    FROM interview_sessions s
    JOIN users u ON u.id = s.user_id
    WHERE s.is_test = 1
    ORDER BY s.started_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-content">
        <?php $interviewOpen = is_interview_open(); ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h1 class="admin-page-title mb-0">Interview Reviews</h1>
            <div class="d-flex align-items-center gap-2">
                <span class="badge <?= $interviewOpen ? 'bg-success' : 'bg-danger' ?>"><?= $interviewOpen ? 'OPEN' : 'CLOSED' ?></span>
                <form method="POST" action="/actions/admin/test_interview.php" class="m-0"
                      onsubmit="return confirm('Start a test run of the interview as yourself? This does NOT open it for candidates, and any previous test run of yours will be cleared.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-play-btn me-1"></i>Test Interview
                    </button>
                </form>
                <form method="POST" action="/actions/admin/toggle_interview.php" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="open" value="<?= $interviewOpen ? '0' : '1' ?>">
                    <input type="hidden" name="redirect" value="/admin/interview_reviews.php">
                    <button type="submit" class="btn btn-sm <?= $interviewOpen ? 'btn-outline-danger' : 'btn-success' ?>"
                        onclick="return confirm('<?= $interviewOpen ? 'Close the interview for all candidates?' : 'Open the interview for all qualified candidates?' ?>')">
                        <i class="bi bi-<?= $interviewOpen ? 'stop' : 'play' ?>-circle me-1"></i><?= $interviewOpen ? 'Stop' : 'Start' ?> Interview
                    </button>
                </form>
            </div>
        </div>
        <?php render_flash(); ?>

        <?php if (empty($rows)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">No interviews submitted yet.</div></div>
        <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>Candidate</th><th>Submitted</th><th>Sample score</th><th>Flags</th><th>Snapshots</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><div class="fw-600"><?= h($r['name']) ?></div><div class="small text-muted"><?= h($r['email']) ?></div></td>
                            <td class="small text-muted"><?= $r['submitted_at'] ? date('M j, Y H:i', strtotime($r['submitted_at'])) : '—' ?></td>
                            <td><?= (int)$r['auto_score'] ?>%</td>
                            <td><?= $r['flags'] > 0 ? '<span class="badge bg-danger-subtle text-danger-emphasis">' . (int)$r['flags'] . '</span>' : '<span class="text-muted">0</span>' ?></td>
                            <td class="text-muted"><?= (int)$r['images'] ?></td>
                            <td>
                                <?php if ($r['status'] === 'reviewed'): ?>
                                <span class="badge-chip chip-published"><?= ucfirst($r['review_decision']) ?></span>
                                <?php else: ?>
                                <span class="badge-chip chip-draft">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="/admin/interview_reviews.php?session_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-search me-1"></i>Review</a>
                                    <form method="POST" action="/actions/admin/reset_interview.php" class="m-0"
                                          onsubmit="return confirm('Delete this interview for <?= h(addslashes($r['name'])) ?> so they can start over? This permanently removes their answers, snapshots and event log and cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="session_id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete &amp; reset"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($testRows)): ?>
        <div class="card mt-4" style="border-style:dashed">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-flask"></i><strong>Admin Test Runs</strong>
                <span class="text-muted small">— not counted as candidates</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Admin</th><th>Started</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($testRows as $r): ?>
                        <tr>
                            <td><div class="fw-600"><?= h($r['name']) ?> <span class="badge bg-secondary ms-1">TEST</span></div><div class="small text-muted"><?= h($r['email']) ?></div></td>
                            <td class="small text-muted"><?= date('M j, Y H:i', strtotime($r['started_at'])) ?></td>
                            <td>
                                <?php if ($r['status'] === 'in_progress'): ?>
                                <span class="badge-chip chip-draft">In progress</span>
                                <?php elseif ($r['status'] === 'reviewed'): ?>
                                <span class="badge-chip chip-published"><?= ucfirst($r['review_decision']) ?></span>
                                <?php else: ?>
                                <span class="badge-chip chip-draft">Submitted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <?php if ($r['status'] === 'in_progress' && (int)$r['user_id'] === (int)current_user()['id']): ?>
                                    <a href="/pages/interview_take.php" class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-right-circle me-1"></i>Resume</a>
                                    <?php elseif ($r['status'] !== 'in_progress'): ?>
                                    <a href="/admin/interview_reviews.php?session_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-search me-1"></i>Review</a>
                                    <?php endif; ?>
                                    <form method="POST" action="/actions/admin/reset_interview.php" class="m-0"
                                          onsubmit="return confirm('Delete this test run?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="session_id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
