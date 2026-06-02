<?php
$pageTitle = 'Coding Interview';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$session = get_interview_session_for_user($user['id']);
if (!$session || $session['status'] !== 'in_progress') {
    header('Location: /pages/interview.php');
    exit;
}

// Server-side timer — refreshing cannot buy more time.
$elapsed   = time() - strtotime($session['started_at']);
$totalSec  = (int)$session['time_limit'] * 60;
$remaining = max(0, $totalSec - $elapsed);
if ($remaining <= 0) {
    header('Location: /actions/submit_interview.php?auto=1');
    exit;
}

$orderedIds = json_decode($session['exercise_ids_json'] ?? '[]', true) ?: [];
$exercises  = get_interview_exercises_in_order($orderedIds);
$answers    = get_interview_answers((int)$session['id']);

// Build candidate-safe task payload (starter + sample cases only — never the
// reference solution or hidden test cases).
$tasks = [];
foreach ($exercises as $i => $ex) {
    $safe = interview_exercise_for_candidate($ex);
    $saved = $answers[(int)$ex['id']] ?? null;
    $safe['index']        = $i;
    $safe['starter']      = $ex['starter_code'];
    $safe['code']         = $saved['submitted_code'] ?? $ex['starter_code'];
    $safe['sample_passed']= $saved ? (int)$saved['sample_passed'] : 0;
    $safe['attempted']    = $saved && trim((string)$saved['submitted_code']) !== '' && $saved['submitted_code'] !== $ex['starter_code'];
    $tasks[] = $safe;
}

$csrfToken = csrf_token();
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/public/css/interview.css">

<div class="iv-header" id="iv-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between gap-3">
        <div class="fw-700 d-none d-md-flex align-items-center gap-2">
            <i class="bi bi-terminal" style="color:var(--primary)"></i> Coding Interview
        </div>
        <div class="iv-progress small text-muted">
            <span id="iv-done-count">0</span> / <?= count($tasks) ?> attempted
        </div>
        <div class="d-flex align-items-center gap-3">
            <div id="iv-timer" class="iv-timer"><?= floor($remaining/60) ?>:<?= str_pad($remaining%60,2,'0',STR_PAD_LEFT) ?></div>
            <button type="button" class="btn btn-primary btn-sm" id="iv-submit-btn">
                <i class="bi bi-send me-1"></i>Submit Interview
            </button>
        </div>
    </div>
</div>

<div class="iv-layout">
    <!-- Task list -->
    <aside class="iv-tasklist" id="iv-tasklist"></aside>

    <!-- Workspace -->
    <main class="iv-workspace">
        <div class="iv-task-head">
            <div>
                <div class="iv-task-kind" id="iv-task-kind"></div>
                <h4 class="mb-0" id="iv-task-title"></h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary" id="iv-task-diff"></span>
                <span class="badge bg-primary" id="iv-task-points"></span>
            </div>
        </div>

        <div class="iv-task-body">
            <p class="iv-task-prompt" id="iv-task-prompt"></p>
            <div class="iv-task-instructions" id="iv-task-instructions"></div>
        </div>

        <div class="iv-editor-wrap">
            <div id="iv-editor" style="height:380px;width:100%"></div>
        </div>

        <div class="iv-actions">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="iv-reset-btn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
                <span class="iv-save-state small text-muted" id="iv-save-state"></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="iv-prev-btn"><i class="bi bi-chevron-left"></i> Prev</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="iv-run-btn"><i class="bi bi-play me-1"></i>Run Tests</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="iv-next-btn">Next <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

        <div class="iv-preview-wrap" id="iv-preview-wrap" style="display:none">
            <div class="iv-preview-label"><i class="bi bi-window me-1"></i>Live preview</div>
            <iframe id="iv-preview" class="iv-preview" title="Live preview"></iframe>
        </div>

        <div class="iv-results" id="iv-results"></div>
    </main>
</div>

<!-- Proctor camera widget -->
<div class="iv-proctor" id="iv-proctor">
    <video id="iv-video" autoplay muted playsinline></video>
    <canvas id="iv-canvas" style="display:none" width="320" height="240"></canvas>
    <div class="iv-proctor-status" id="iv-proctor-status">
        <span class="text-muted"><i class="bi bi-camera-video me-1"></i>Connecting…</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
<script src="/public/js/interview_harness.js"></script>
<script src="/public/js/interview_preview.js"></script>
<script>
(function () {
    var SESSION_ID = <?= (int)$session['id'] ?>;
    var CSRF       = <?= json_encode($csrfToken) ?>;
    var TASKS      = <?= json_encode($tasks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var REMAINING  = <?= (int)$remaining ?>;

    var current   = 0;
    var editor    = null;
    var saveTimer = null;
    var dirty     = {};               // exercise_id -> bool (unsaved)
    var codeMap   = {};               // exercise_id -> latest code
    TASKS.forEach(function (t) { codeMap[t.id] = t.code; });

    /* ── Integrity event logging ─────────────────────────────── */
    function logEvent(type, detail) {
        try {
            fetch('/actions/log_interview_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: SESSION_ID, csrf_token: CSRF, event_type: type, detail: detail || '' }),
                keepalive: true
            }).catch(function () {});
        } catch (e) {}
    }
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) logEvent('tab_hidden', 'Candidate switched away from the interview tab');
    });
    window.addEventListener('blur', function () { logEvent('window_blur', ''); });
    document.addEventListener('copy', function () { logEvent('copy', 'Copy from the interview'); });

    /* ── Task list rendering ─────────────────────────────────── */
    var listEl = document.getElementById('iv-tasklist');
    function taskIcon(t) {
        return t.kind === 'coding' ? 'code-slash' : (t.kind === 'debugging' ? 'bug' : 'window');
    }
    function buildList() {
        var codingN = 0, debugN = 0, webN = 0, html = '';
        TASKS.forEach(function (t, i) {
            var label = t.kind === 'coding' ? 'Coding ' + (++codingN)
                      : (t.kind === 'debugging' ? 'Debug ' + (++debugN) : 'Web ' + (++webN));
            html += '<button type="button" class="iv-task-item" data-i="' + i + '">' +
                        '<span class="iv-task-dot" data-dot="' + t.id + '"></span>' +
                        '<span class="iv-task-item-label">' + label + '</span>' +
                        '<i class="bi bi-' + taskIcon(t) + ' iv-task-item-icon"></i>' +
                    '</button>';
        });
        listEl.innerHTML = html;
        Array.prototype.forEach.call(listEl.querySelectorAll('.iv-task-item'), function (btn) {
            btn.addEventListener('click', function () { go(parseInt(btn.dataset.i, 10)); });
        });
    }
    function refreshDots() {
        var done = 0;
        TASKS.forEach(function (t) {
            var dot = listEl.querySelector('[data-dot="' + t.id + '"]');
            var attempted = (codeMap[t.id] || '') !== (t.starter || '') && (codeMap[t.id] || '').trim() !== '';
            if (attempted) { done++; dot && dot.classList.add('done'); }
            else { dot && dot.classList.remove('done'); }
        });
        document.getElementById('iv-done-count').textContent = done;
        Array.prototype.forEach.call(listEl.querySelectorAll('.iv-task-item'), function (btn, i) {
            btn.classList.toggle('active', i === current);
        });
    }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]; }); }

    function isProject(t) { return t.kind === 'project'; }
    function monacoLangFor(t) {
        var e = t.exercise_type || 'javascript';
        return e === 'javascript' ? 'javascript' : (e === 'css' ? 'css' : 'html');
    }
    function reqList(t) {
        return (t.instructions || '').split('\n').map(function (l) { return l.trim(); })
            .filter(function (l) { return /^\d+\./.test(l); })
            .map(function (l) { return l.replace(/^\d+\.\s*/, ''); });
    }
    function renderPreview(t, code) {
        var wrap = document.getElementById('iv-preview-wrap');
        var iframe = document.getElementById('iv-preview');
        try {
            var html = window.interviewBuildPreview(code, t.exercise_type, { token: CSRF, exerciseId: t.id });
            iframe.src = URL.createObjectURL(new Blob([html], { type: 'text/html' }));
        } catch (e) {}
        wrap.style.display = 'block';
    }

    /* ── Switch to a task ────────────────────────────────────── */
    function go(i) {
        if (i < 0 || i >= TASKS.length) return;
        flushCurrent();
        current = i;
        var t = TASKS[i];
        var kindBadge = t.kind === 'coding'
            ? '<span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-code-slash me-1"></i>Coding Task</span>'
            : (t.kind === 'debugging'
                ? '<span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-bug-fill me-1"></i>Debug Task</span>'
                : '<span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-window me-1"></i>Applied Web Task</span>');
        document.getElementById('iv-task-kind').innerHTML = kindBadge;
        document.getElementById('iv-task-title').textContent = t.title;
        document.getElementById('iv-task-diff').textContent = (t.difficulty || '').toUpperCase();
        document.getElementById('iv-task-points').textContent = t.points + ' pts';
        document.getElementById('iv-task-prompt').textContent = t.prompt;
        var ins = (t.instructions || '').split('\n').map(function (l) { return l.trim(); }).filter(Boolean);
        var note = t.kind === 'debugging'
            ? '<div class="iv-debug-note"><i class="bi bi-bug-fill me-1"></i>The function below is broken — fix it so all requirements hold.</div>'
            : (isProject(t) ? '<div class="iv-applied-note"><i class="bi bi-window me-1"></i>Build this in the editor, then <strong>Run &amp; Check</strong> to preview it live below.</div>' : '');
        document.getElementById('iv-task-instructions').innerHTML =
            note + '<strong class="small">Requirements:</strong><ul class="small mb-0 mt-1">' +
            ins.map(function (l) { return '<li>' + esc(l.replace(/^\d+\.\s*/, '')) + '</li>'; }).join('') + '</ul>';
        document.getElementById('iv-results').innerHTML = '';

        if (editor) {
            if (window.monaco) monaco.editor.setModelLanguage(editor.getModel(), monacoLangFor(t));
            editor.setValue(codeMap[t.id] || t.starter || '');
        }

        var runBtn = document.getElementById('iv-run-btn');
        if (isProject(t)) {
            runBtn.innerHTML = '<i class="bi bi-play me-1"></i>Run &amp; Check';
            renderPreview(t, codeMap[t.id] || t.starter || '');
        } else {
            runBtn.innerHTML = '<i class="bi bi-play me-1"></i>Run Tests';
            document.getElementById('iv-preview-wrap').style.display = 'none';
        }
        refreshDots();
    }

    /* ── Autosave ────────────────────────────────────────────── */
    function setSaveState(txt, ok) {
        var el = document.getElementById('iv-save-state');
        el.textContent = txt;
        el.style.color = ok ? 'var(--success)' : 'var(--text-muted)';
    }
    function flushCurrent() {
        var t = TASKS[current];
        if (!t || !editor) return;
        codeMap[t.id] = editor.getValue();
        if (dirty[t.id]) saveAnswer(t.id);
    }
    function saveAnswer(exerciseId, samplePassed, sampleTotal) {
        var fd = {
            session_id: SESSION_ID, csrf_token: CSRF, exercise_id: exerciseId,
            code: codeMap[exerciseId] || ''
        };
        if (typeof samplePassed === 'number') { fd.sample_passed = samplePassed; fd.sample_total = sampleTotal; }
        setSaveState('Saving…', false);
        return fetch('/actions/save_interview_answer.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(fd), keepalive: true
        }).then(function (r) { return r.json(); }).then(function (d) {
            dirty[exerciseId] = false;
            setSaveState(d && d.ok ? 'Saved' : 'Save failed', !!(d && d.ok));
            refreshDots();
        }).catch(function () { setSaveState('Save failed', false); });
    }

    /* ── Run (sample tests, or preview + checklist for applied tasks) ─── */
    function runProjectCheck(t, code) {
        renderPreview(t, code);
        var reqs = reqList(t);
        var resEl = document.getElementById('iv-results');
        if (!reqs.length) { resEl.innerHTML = '<div class="iv-res-info">Preview updated — this task is reviewed manually.</div>'; saveAnswer(t.id); return; }
        var results = window.interviewCheckRequirements(code, reqs);
        var met = results.filter(function (r) { return r.met; }).length;
        var head = '<div class="iv-res-head ' + (met === reqs.length ? 'ok' : 'warn') + '">' +
                   '<i class="bi bi-' + (met === reqs.length ? 'check-circle-fill' : 'exclamation-circle-fill') + ' me-1"></i>' +
                   met + ' / ' + reqs.length + ' requirements detected</div>';
        var rows = results.map(function (r) {
            return '<div class="iv-res-row ' + (r.met ? 'pass' : 'fail') + '">' +
                '<i class="bi bi-' + (r.met ? 'check-circle-fill text-success' : 'circle text-muted') + ' me-1"></i>' + esc(r.text) + '</div>';
        }).join('');
        resEl.innerHTML = head + rows + '<div class="iv-res-info mt-2"><i class="bi bi-info-circle me-1"></i>This checklist is a guide — a reviewer scores your work and the live preview.</div>';
        saveAnswer(t.id, met, reqs.length);
    }

    document.getElementById('iv-run-btn').addEventListener('click', function () {
        var t = TASKS[current];
        var code = editor.getValue();
        codeMap[t.id] = code; dirty[t.id] = true;
        if (isProject(t)) { runProjectCheck(t, code); return; }
        var resEl = document.getElementById('iv-results');
        if (!t.sample_cases || !t.sample_cases.length) {
            resEl.innerHTML = '<div class="iv-res-info">No sample cases for this task — your submission is reviewed manually.</div>';
            saveAnswer(t.id);
            return;
        }
        resEl.innerHTML = '<div class="iv-res-info"><i class="bi bi-hourglass-split me-1"></i>Running…</div>';
        window.runInterviewTests(code, t.entry, t.sample_cases).then(function (results) {
            var passed = results.filter(function (r) { return r.pass; }).length;
            var total  = t.sample_cases.length;
            var head = '<div class="iv-res-head ' + (passed === total ? 'ok' : 'warn') + '">' +
                       '<i class="bi bi-' + (passed === total ? 'check-circle-fill' : 'exclamation-circle-fill') + ' me-1"></i>' +
                       passed + ' / ' + total + ' sample tests passed</div>';
            var rows = results.map(function (r) {
                if (r.fatal) return '<div class="iv-res-row fail">Error: ' + esc(r.fatal) + '</div>';
                return '<div class="iv-res-row ' + (r.pass ? 'pass' : 'fail') + '">' +
                    '<i class="bi bi-' + (r.pass ? 'check' : 'x') + '-lg me-1"></i>' +
                    '<code>' + esc(t.entry) + '(' + esc(JSON.stringify(r.args).slice(1, -1)) + ')</code> ' +
                    '→ expected <code>' + esc(JSON.stringify(r.expected)) + '</code>' +
                    (r.pass ? '' : (r.error ? ', threw <code>' + esc(r.error) + '</code>' : ', got <code>' + esc(JSON.stringify(r.got)) + '</code>')) +
                    '</div>';
            }).join('');
            resEl.innerHTML = head + rows;
            saveAnswer(t.id, passed, total);
        });
    });

    document.getElementById('iv-reset-btn').addEventListener('click', function () {
        var t = TASKS[current];
        if (!confirm('Reset this task to its starting code? Your changes here will be lost.')) return;
        editor.setValue(t.starter || '');
        codeMap[t.id] = t.starter || ''; dirty[t.id] = true;
        saveAnswer(t.id);
        document.getElementById('iv-results').innerHTML = '';
        if (isProject(t)) renderPreview(t, t.starter || '');
    });
    document.getElementById('iv-prev-btn').addEventListener('click', function () { go(current - 1); });
    document.getElementById('iv-next-btn').addEventListener('click', function () { go(current + 1); });

    /* ── Submit ──────────────────────────────────────────────── */
    var submitted = false;
    function submitInterview(auto) {
        if (submitted) return;
        if (!auto && !confirm('Submit your interview for review? You cannot make further changes.')) return;
        submitted = true;
        document.getElementById('iv-submit-btn').disabled = true;
        flushCurrent();
        stopCamera();
        // Give the final autosave a moment, then post the submit.
        setTimeout(function () {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/actions/submit_interview.php';
            form.innerHTML = '<input type="hidden" name="csrf_token" value="' + CSRF + '">' +
                             '<input type="hidden" name="session_id" value="' + SESSION_ID + '">' +
                             (auto ? '<input type="hidden" name="auto" value="1">' : '');
            document.body.appendChild(form);
            form.submit();
        }, 400);
    }
    document.getElementById('iv-submit-btn').addEventListener('click', function () { submitInterview(false); });

    /* ── Timer ───────────────────────────────────────────────── */
    var secs = REMAINING;
    var timerEl = document.getElementById('iv-timer');
    var tick = setInterval(function () {
        secs = Math.max(0, secs - 1);
        timerEl.textContent = Math.floor(secs / 60) + ':' + String(secs % 60).padStart(2, '0');
        timerEl.classList.toggle('danger', secs <= 60);
        timerEl.classList.toggle('warning', secs > 60 && secs <= 300);
        if (secs <= 0) { clearInterval(tick); submitInterview(true); }
    }, 1000);

    /* ── Proctor camera ──────────────────────────────────────── */
    var video = document.getElementById('iv-video');
    var canvas = document.getElementById('iv-canvas');
    var statusEl = document.getElementById('iv-proctor-status');
    var stream = null, capTimer = null;
    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 320 }, height: { ideal: 240 } }, audio: false })
            .then(function (s) {
                stream = s; video.srcObject = s;
                statusEl.innerHTML = '<span class="iv-rec-dot"></span><span class="text-danger fw-600" style="font-size:.7rem">LIVE</span>';
                proctor('camera_granted');
                capTimer = setTimeout(captureLoop, 30000);
            })
            .catch(function () {
                statusEl.innerHTML = '<span class="text-warning" style="font-size:.7rem"><i class="bi bi-camera-video-off me-1"></i>Camera unavailable</span>';
                logEvent('camera_denied', 'Candidate did not grant camera access');
            });
    }
    function captureLoop() {
        if (!stream || !stream.active) return;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, 320, 240);
        proctor('capture', canvas.toDataURL('image/jpeg', 0.7));
        capTimer = setTimeout(captureLoop, (45 + Math.random() * 45) * 1000);
    }
    function proctor(action, image) {
        fetch('/actions/interview_proctor.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action, session_id: SESSION_ID, csrf_token: CSRF, image: image || '' })
        }).catch(function () {});
    }
    function stopCamera() {
        if (capTimer) clearTimeout(capTimer);
        if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
    }

    /* ── Monaco init ─────────────────────────────────────────── */
    buildList();
    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        monaco.editor.defineTheme('hackathonDark', {
            base: 'vs-dark', inherit: true, rules: [],
            colors: { 'editor.background': '#0D1117', 'editor.foreground': '#E0E0E0', 'editorCursor.foreground': '#F8B526' }
        });
        editor = monaco.editor.create(document.getElementById('iv-editor'), {
            value: '', language: 'javascript', theme: 'hackathonDark',
            minimap: { enabled: false }, fontSize: 14, fontFamily: "'JetBrains Mono','Fira Code',monospace",
            scrollBeyondLastLine: false, automaticLayout: true, tabSize: 2
        });
        editor.onDidChangeModelContent(function () {
            var t = TASKS[current];
            codeMap[t.id] = editor.getValue();
            dirty[t.id] = true;
            setSaveState('Editing…', false);
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function () { saveAnswer(t.id); }, 2000);
        });
        if (editor.onDidPaste) editor.onDidPaste(function () { logEvent('paste', 'Code pasted into the editor'); });
        go(0);
    });

    window.addEventListener('beforeunload', function (e) {
        if (!submitted) { flushCurrent(); logEvent('left_page', 'Closed or reloaded the interview tab'); }
    });

    startCamera();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
