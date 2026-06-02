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

    // Build the JS payload: full test cases (sample + hidden) + candidate code.
    $jsTasks = [];
    foreach ($exercises as $i => $ex) {
        $tc  = json_decode($ex['test_cases_json'] ?? '{}', true) ?: [];
        $ans = $answers[(int)$ex['id']] ?? null;
        $jsTasks[] = [
            'exercise_id' => (int)$ex['id'],
            'kind'        => $ex['kind'],
            'title'       => $ex['title'],
            'entry'       => $tc['entry'] ?? '',
            'cases'       => $tc['cases'] ?? [],
            'points'      => (int)$ex['points'],
            'code'        => $ans['submitted_code'] ?? $ex['starter_code'],
        ];
    }

    require_once __DIR__ . '/../includes/header.php';
    ?>
    <link rel="stylesheet" href="/public/css/interview.css">
    <div class="admin-layout">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>
        <div class="admin-content">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/admin/interview_reviews.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>All Reviews</a>
                <h1 class="admin-page-title mb-0">Interview — <?= h($candidate['name']) ?></h1>
                <span class="badge-chip <?= $session['status'] === 'reviewed' ? 'chip-published' : 'chip-draft' ?> ms-auto">
                    <?= $session['status'] === 'reviewed' ? 'Reviewed — ' . ucfirst($session['review_decision']) : 'Pending review' ?>
                </span>
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
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($images as $img): ?>
                        <a href="/public/<?= h($img['image_path']) ?>" target="_blank" title="<?= date('H:i:s', strtotime($img['captured_at'])) ?>">
                            <img src="/public/<?= h($img['image_path']) ?>" style="width:120px;height:90px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border)">
                        </a>
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
                <div class="card mb-3" data-task="<?= $i ?>">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge <?= $t['kind'] === 'coding' ? 'bg-info-subtle text-info-emphasis' : 'bg-danger-subtle text-danger-emphasis' ?>">
                                <?= $t['kind'] === 'coding' ? 'Coding' : 'Debug' ?>
                            </span>
                            <strong class="ms-2"><?= h($t['title']) ?></strong>
                        </div>
                        <div class="small text-muted" id="auto-result-<?= $i ?>">Not run</div>
                    </div>
                    <div class="card-body">
                        <pre class="iv-code-view"><code><?= h($t['code']) ?></code></pre>
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

    <style>
    .iv-code-view { background:#0D1117; color:#e0e0e0; border:1px solid var(--border); border-radius:var(--radius); padding:1rem; font-family:var(--font-mono); font-size:.82rem; max-height:320px; overflow:auto; white-space:pre; }
    .iv-decision-opt { display:flex; align-items:center; gap:.4rem; padding:.5rem .9rem; border:1px solid var(--border); border-radius:var(--radius); cursor:pointer; }
    </style>

    <script src="/public/js/interview_harness.js"></script>
    <script>
    (function () {
        var TASKS = <?= json_encode($jsTasks, JSON_UNESCAPED_SLASHES) ?>;
        function esc(s){return String(s==null?'':s).replace(/[&<>]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];});}

        function runTask(i) {
            var t = TASKS[i];
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
$rows = db()->query("
    SELECT s.*, u.name, u.email,
        (SELECT COUNT(*) FROM interview_events e WHERE e.session_id = s.id AND e.event_type != 'camera_granted') AS flags,
        (SELECT COUNT(*) FROM interview_proctor_images p WHERE p.session_id = s.id) AS images
    FROM interview_sessions s
    JOIN users u ON u.id = s.user_id
    WHERE s.status IN ('submitted','reviewed')
    ORDER BY (s.status = 'submitted') DESC, s.submitted_at DESC
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
                            <td><a href="/admin/interview_reviews.php?session_id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-search me-1"></i>Review</a></td>
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
