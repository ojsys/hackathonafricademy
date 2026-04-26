<?php
$pageTitle = 'Final Exam';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$isAdminPreview = is_admin() && isset($_GET['preview']);

if ($isAdminPreview) {
    // Preview mode: load exam and questions directly, no attempt needed
    $exam = get_qualifying_exam();
    if (!$exam) { header('Location: /pages/qualifying_exam.php'); exit; }
    $questions      = get_qualifying_questions($exam['id']);
    $attempt        = null;
    $elapsed        = 0;
    $remaining      = 0;
    $proctorSession = null;
    $csrfToken      = csrf_token();
} else {
    // Normal mode: require an active attempt
    $attempt = get_active_qualifying_attempt($user['id']);
    if (!$attempt) {
        header('Location: /pages/qualifying_exam.php');
        exit;
    }

    $examStmt = db()->prepare('SELECT * FROM qualifying_exam WHERE id = ?');
    $examStmt->execute([$attempt['exam_id']]);
    $exam = $examStmt->fetch();
    if (!$exam) { header('Location: /pages/qualifying_exam.php'); exit; }

    // Load questions — shuffle once and store order in session
    start_session();
    $sessionKey = 'q_order_' . $attempt['id'];
    if (empty($_SESSION[$sessionKey])) {
        $all = get_qualifying_questions($exam['id']);
        $ids = array_column($all, 'id');
        shuffle($ids);
        $_SESSION[$sessionKey] = $ids;
    }
    $orderedIds = $_SESSION[$sessionKey];

    // Fetch questions in shuffled order
    $placeholders = implode(',', array_fill(0, count($orderedIds), '?'));
    $stmt = db()->prepare("SELECT * FROM qualifying_questions WHERE id IN ($placeholders)");
    $stmt->execute($orderedIds);
    $byId = [];
    foreach ($stmt->fetchAll() as $q) { $byId[$q['id']] = $q; }
    $questions = array_map(fn($id) => $byId[$id], array_filter($orderedIds, fn($id) => isset($byId[$id])));

    // Time remaining (server-side calculation prevents cheating via refresh)
    $elapsed   = time() - strtotime($attempt['started_at']);
    $totalSec  = $exam['time_limit'] * 60;
    $remaining = max(0, $totalSec - $elapsed);

    // Auto-submit if time already expired
    if ($remaining <= 0) {
        header('Location: /actions/submit_qualifying_exam.php?auto=1&attempt_id=' . $attempt['id']);
        exit;
    }

    $proctorSession = get_proctor_session($attempt['id']);
    $csrfToken      = csrf_token();
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.proctor-widget {
    position: fixed;
    bottom: 1.25rem;
    right: 1.25rem;
    z-index: 1050;
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 0.5rem;
    width: 176px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
.proctor-widget video {
    width: 160px;
    height: 120px;
    border-radius: calc(var(--radius) - 2px);
    background: #000;
    display: block;
    object-fit: cover;
}
.proctor-status {
    font-size: 0.7rem;
    margin-top: 0.35rem;
    text-align: center;
}
.rec-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--danger);
    animation: blink 1.2s infinite;
    margin-right: 3px;
}
.exam-header {
    position: sticky; top: 0; z-index: 100;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0.75rem 0;
}
.timer-display {
    font-family: var(--font-mono);
    font-size: 1.35rem;
    font-weight: 700;
    min-width: 70px;
    text-align: center;
}
.timer-display.warning { color: var(--warning); }
.timer-display.danger  { color: var(--danger); animation: blink 1s infinite; }
.q-dot {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--bg);
    border: 1px solid var(--border);
    font-size: 0.78rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.15s;
    flex-shrink: 0;
}
.q-dot.answered { background: var(--primary-glow); border-color: var(--primary); color: var(--primary); }
.q-dot:hover { border-color: var(--primary); }
.exam-question {
    background: var(--surface);
    border: 1px solid var(--border);
    border-left: 3px solid var(--warning);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    scroll-margin-top: 80px;
}
.exam-option {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    margin-bottom: 0.5rem;
}
.exam-option:hover { border-color: var(--primary); background: var(--primary-glow); }
.exam-option input[type="radio"] { accent-color: var(--primary); flex-shrink: 0; }
.exam-option input[type="radio"]:checked ~ span { color: var(--primary); font-weight: 600; }
</style>

<?php if ($isAdminPreview): ?>
<div class="alert alert-warning rounded-0 border-0 border-bottom border-warning d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-eye-fill"></i>
    <span><strong>Admin Preview</strong> — Read-only view. Timer and proctoring are disabled. No attempt is recorded.</span>
    <a href="/pages/qualifying_exam.php" class="btn btn-sm btn-outline-warning ms-auto">Exit Preview</a>
</div>
<?php endif; ?>

<div class="exam-header" id="exam-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between gap-3">
        <div class="fw-700 d-none d-md-block"><?= h($exam['title']) ?></div>
        <div class="d-flex align-items-center gap-2 flex-wrap" style="max-width:60%">
            <?php foreach ($questions as $i => $_): ?>
            <div class="q-dot" id="dot-<?= $i+1 ?>" onclick="scrollToQ(<?= $i+1 ?>)" title="Question <?= $i+1 ?>"><?= $i+1 ?></div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if ($isAdminPreview): ?>
            <div class="timer-display text-muted">Preview</div>
            <a href="/pages/qualifying_exam.php" class="btn btn-outline-secondary btn-sm">Exit</a>
            <?php else: ?>
            <div id="timer" class="timer-display"><?= floor($remaining/60) ?>:<?= str_pad($remaining%60,2,'0',STR_PAD_LEFT) ?></div>
            <button form="exam-form" type="submit" class="btn btn-primary btn-sm" id="submit-btn">
                <i class="bi bi-send me-1"></i>Submit
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-4" style="max-width:820px">
    <?php if (!$isAdminPreview): ?>
    <form id="exam-form" action="/actions/submit_qualifying_exam.php" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="attempt_id" value="<?= $attempt['id'] ?>">
        <input type="hidden" name="time_taken" id="time-taken" value="<?= $elapsed ?>">
    <?php endif; ?>

        <?php foreach ($questions as $i => $q): $num = $i + 1; ?>
        <?php $options = json_decode($q['options_json'], true) ?? []; ?>
        <div class="exam-question" id="q-<?= $num ?>">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="small fw-700 text-muted text-uppercase" style="letter-spacing:.08em">
                        <?= $q['course_tag'] ? h($q['course_tag']) . ' — ' : '' ?>Question <?= $num ?> of <?= count($questions) ?>
                    </span>
                </div>
                <span class="badge" style="background:var(--surface-hover);color:var(--text-muted)"><?= $q['points'] ?> pt<?= $q['points'] != 1 ? 's' : '' ?></span>
            </div>
            <p class="fw-600 mb-3" style="font-size:1.05rem"><?= nl2br(h($q['question_text'])) ?></p>
            <?php foreach ($options as $idx => $opt): ?>
            <label class="exam-option" <?= !$isAdminPreview ? 'onclick="markAnswered(' . $num . ')"' : '' ?>>
                <input type="radio" name="answer_<?= $q['id'] ?>" value="<?= $idx ?>" <?= $isAdminPreview ? 'disabled' : '' ?>>
                <span><?= h($opt) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5 pb-5">
            <a href="/pages/qualifying_exam.php" class="btn btn-outline-secondary">
                <i class="bi bi-<?= $isAdminPreview ? 'arrow-left' : 'x-lg' ?> me-1"></i><?= $isAdminPreview ? 'Back to Exam' : 'Cancel' ?>
            </a>
            <?php if (!$isAdminPreview): ?>
            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn-bottom">
                <i class="bi bi-send me-1"></i>Submit Exam
            </button>
            <?php endif; ?>
        </div>

    <?php if (!$isAdminPreview): ?>
    </form>
    <?php endif; ?>
</div>

<!-- Proctor camera widget (hidden in preview mode) -->
<?php if (!$isAdminPreview): ?>
<div class="proctor-widget" id="proctor-widget">
    <video id="proctor-video" autoplay muted playsinline></video>
    <canvas id="capture-canvas" style="display:none" width="320" height="240"></canvas>
    <div class="proctor-status" id="proctor-status">
        <span class="text-muted"><i class="bi bi-camera-video me-1"></i>Connecting…</span>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    const isAdminPreview = <?= $isAdminPreview ? 'true' : 'false' ?>;

    // ── Question nav (always active) ──────────────────────────
    window.scrollToQ = function(num) {
        document.getElementById('q-' + num)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
    window.markAnswered = function(num) {
        document.getElementById('dot-' + num)?.classList.add('answered');
    };

    if (isAdminPreview) return; // skip timer and proctoring in preview mode

    // ── Timer ─────────────────────────────────────────────────
    let secondsLeft = <?= (int)$remaining ?>;
    const timerEl   = document.getElementById('timer');
    const takenEl   = document.getElementById('time-taken');
    const examLimit = <?= (int)($exam['time_limit'] * 60) ?>;

    const tick = setInterval(() => {
        secondsLeft = Math.max(0, secondsLeft - 1);
        const m = Math.floor(secondsLeft / 60);
        const s = secondsLeft % 60;
        timerEl.textContent = m + ':' + String(s).padStart(2, '0');
        takenEl.value = examLimit - secondsLeft;

        timerEl.classList.toggle('danger',  secondsLeft <= 60);
        timerEl.classList.toggle('warning', secondsLeft > 60 && secondsLeft <= 300);

        if (secondsLeft <= 0) {
            clearInterval(tick);
            stopCamera();
            document.getElementById('exam-form').submit();
        }
    }, 1000);

    // ── Proctoring ────────────────────────────────────────────
    const ATTEMPT_ID  = <?= (int)($attempt['id'] ?? 0) ?>;
    const CSRF_TOKEN  = <?= json_encode($csrfToken) ?>;
    const SESSION_ID  = <?= (int)($proctorSession['id'] ?? 0) ?>;
    const video       = document.getElementById('proctor-video');
    const canvas      = document.getElementById('capture-canvas');
    const statusEl    = document.getElementById('proctor-status');
    let videoStream   = null;
    let captureTimer  = null;

    async function startCamera() {
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 320 }, height: { ideal: 240 } },
                audio: false
            });
            video.srcObject = videoStream;
            statusEl.innerHTML = '<span class="rec-dot"></span><span class="text-danger fw-600" style="font-size:.7rem">LIVE</span>';

            fetch('/actions/save_proctor_image.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'camera_granted', attempt_id: ATTEMPT_ID, session_id: SESSION_ID, csrf_token: CSRF_TOKEN })
            }).catch(() => {});

            captureTimer = setTimeout(captureAndSchedule, 30000);
        } catch (err) {
            statusEl.innerHTML = '<span class="text-warning" style="font-size:.7rem"><i class="bi bi-camera-video-off me-1"></i>Camera unavailable</span>';
        }
    }

    function captureAndSchedule() {
        if (!videoStream?.active) return;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, 320, 240);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.75);

        fetch('/actions/save_proctor_image.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'capture', image: dataUrl, attempt_id: ATTEMPT_ID, session_id: SESSION_ID, csrf_token: CSRF_TOKEN })
        }).catch(() => {});

        const delay = (45 + Math.random() * 45) * 1000;
        captureTimer = setTimeout(captureAndSchedule, delay);
    }

    function stopCamera() {
        if (captureTimer) clearTimeout(captureTimer);
        if (videoStream) videoStream.getTracks().forEach(t => t.stop());
    }

    document.getElementById('exam-form').addEventListener('submit', function (e) {
        const unanswered = <?= count($questions) ?> - document.querySelectorAll('.q-dot.answered').length;
        if (unanswered > 0) {
            if (!confirm(unanswered + ' question(s) unanswered. Submit anyway?')) {
                e.preventDefault();
                return;
            }
        }
        stopCamera();
        document.getElementById('submit-btn').disabled = true;
        document.getElementById('submit-btn-bottom').disabled = true;
    });

    startCamera();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
