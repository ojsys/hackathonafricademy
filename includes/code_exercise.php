<?php
/**
 * Code Exercise Component — Monaco Editor powered
 * Include this in lesson pages to render code exercises
 */

function render_code_exercise(array $exercise, ?array $submission = null): void {
    $exerciseId = $exercise['id'];
    $type = $exercise['exercise_type'] ?? 'html';
    $hints = $exercise['hints'] ? explode('|', $exercise['hints']) : [];
    $langMap = ['html' => 'html', 'css' => 'css', 'javascript' => 'javascript', 'combined' => 'html'];
    $monacoLang = $langMap[$type] ?? 'html';
    $initialCode = $submission['submitted_code'] ?? $exercise['starter_code'] ?? '';
?>
<div class="code-exercise" data-exercise-id="<?= $exerciseId ?>" data-testid="code-exercise-<?= $exerciseId ?>">
    <div class="code-exercise-header">
        <h4>
            <i class="bi bi-code-slash me-2"></i>
            <?= h($exercise['title']) ?>
        </h4>
        <div class="d-flex align-items-center gap-2">
            <span class="badge <?= $exercise['difficulty'] === 'easy' ? 'bg-success' : ($exercise['difficulty'] === 'medium' ? 'bg-warning' : 'bg-danger') ?>">
                <?= ucfirst($exercise['difficulty']) ?>
            </span>
            <span class="badge bg-secondary"><?= strtoupper($type) ?></span>
            <span class="badge bg-primary"><?= $exercise['points'] ?> pts</span>
            <?php if ($submission && $submission['is_correct']): ?>
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="p-3 border-bottom" style="background: var(--bg);">
        <p class="mb-2"><?= h($exercise['description']) ?></p>
        <div class="small text-muted exercise-instructions">
            <strong>Instructions:</strong><br>
            <?= nl2br(h($exercise['instructions'])) ?>
        </div>
    </div>
    
    <div class="code-exercise-body">
        <div class="code-editor-pane">
            <div id="monaco-editor-<?= $exerciseId ?>" 
                 class="monaco-editor-mount"
                 data-lang="<?= h($monacoLang) ?>"
                 data-exercise-id="<?= $exerciseId ?>"
                 data-testid="code-editor-<?= $exerciseId ?>"
                 style="height: 520px; width: 100%;"></div>
            <textarea class="code-editor-textarea" 
                      id="code-textarea-<?= $exerciseId ?>"
                      data-exercise-type="<?= h($type) ?>"
                      data-starter-code="<?= h($exercise['starter_code'] ?? '') ?>"
                      style="display: none;"
            ><?= h($initialCode) ?></textarea>
        </div>
        <div class="code-preview-pane">
            <iframe class="code-preview-iframe" data-testid="code-preview-<?= $exerciseId ?>"></iframe>
        </div>
    </div>
    
    <div class="code-exercise-footer">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($hints)): ?>
            <span class="hint-toggle" data-testid="hint-toggle-<?= $exerciseId ?>">
                <i class="bi bi-lightbulb me-1"></i> Show Hint
            </span>
            <?php endif; ?>
            
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetExercise(<?= $exerciseId ?>)">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="runExercise(<?= $exerciseId ?>)">
                <i class="bi bi-play me-1"></i> Run
            </button>
            <button type="button" class="btn btn-sm btn-primary submit-exercise-btn" onclick="submitExercise(<?= $exerciseId ?>)" data-testid="submit-exercise-<?= $exerciseId ?>">
                <i class="bi bi-check2 me-1"></i> Submit
            </button>
        </div>
    </div>
    
    <?php if (!empty($hints)): ?>
    <div class="hint-content">
        <strong class="small">Hints:</strong>
        <ul class="mb-0 mt-2 small">
            <?php foreach ($hints as $hint): ?>
            <li><?= h(trim($hint)) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="exercise-feedback" data-testid="exercise-feedback-<?= $exerciseId ?>"></div>
</div>
<?php
}

/**
 * Render all exercises for a lesson
 */
function render_lesson_exercises(int $lessonId, int $userId): void {
    $exercises = get_exercises_for_lesson($lessonId);
    
    if (empty($exercises)) return;
    
    echo '<div>';
    echo '<h3><i class="bi bi-code-square me-2 text-primary"></i>Code Exercises</h3>';
    echo '<p class="text-muted">Practice what you\'ve learned with these hands-on exercises.</p>';
    
    foreach ($exercises as $exercise) {
        $submission = get_user_exercise_submission($userId, $exercise['id']);
        render_code_exercise($exercise, $submission);
    }
    
    echo '</div>';
}

/**
 * Output Monaco Editor loader script (call once per page that has exercises)
 */
function render_monaco_loader(): void {
?>
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
<script>
(function () {
    /* ─── Build HTML for the live preview iframe ─────────────────────────── */
    function buildHtml(code, type) {
        var isDoc = /^\s*(<!doctype|<html)/i.test(code);
        if (type === 'css') {
            return '<!DOCTYPE html><html><head><meta charset="utf-8">' +
                   '<style>body{font-family:sans-serif;padding:1.25rem;margin:0}' + code + '</style>' +
                   '</head><body>' +
                   '<h1>Heading One</h1><h2>Heading Two</h2>' +
                   '<p>This is a sample paragraph of text.</p>' +
                   '<button>Button</button> <a href="#">A link</a>' +
                   '<ul><li>Item one</li><li>Item two</li><li>Item three</li></ul>' +
                   '<div class="box card" style="margin-top:1rem;padding:1rem;border:1px solid #ccc">Sample div / card</div>' +
                   '</body></html>';
        }
        if (type === 'javascript') {
            var safe = code.replace(/<\/script>/gi, '<\\/script>');
            return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' +
                   'body{font-family:system-ui;padding:1rem;background:#0d1117;color:#e0e0e0;margin:0}' +
                   '#__out{background:#161b22;padding:1rem;border-radius:6px;font-family:monospace;white-space:pre-wrap;font-size:.9rem;min-height:3rem}' +
                   '.lbl{font-size:.7rem;color:#8b949e;text-transform:uppercase;margin-bottom:.5rem}' +
                   '</style></head><body>' +
                   '<div class="lbl">Console output</div><div id="__out"></div>' +
                   '<script>(function(){' +
                   'var o=document.getElementById("__out");' +
                   'var _l=console.log,_e=console.error;' +
                   'console.log=function(){o.textContent+=Array.from(arguments).map(function(x){return typeof x==="object"?JSON.stringify(x,null,2):String(x);}).join(" ")+"\n";_l.apply(console,arguments);};' +
                   'console.error=function(){o.innerHTML+=\'<span style="color:#f87171">\'+Array.from(arguments).map(String).join(" ")+"</span>\n";_e.apply(console,arguments);};' +
                   'try{' + safe + '}catch(e){o.innerHTML+=\'<span style="color:#f87171">Error: \'+e.message+\'</span>\';}' +
                   '}());<\/script></body></html>';
        }
        /* html / combined */
        if (isDoc) return code;
        return '<!DOCTYPE html><html><head><meta charset="utf-8">' +
               '<style>body{font-family:sans-serif;padding:1rem;margin:0}</style>' +
               '</head><body>' + code + '</body></html>';
    }

    function doPreview(code, type, iframe) {
        try {
            var blob = new Blob([buildHtml(code, type)], { type: 'text/html' });
            iframe.src = URL.createObjectURL(blob);
        } catch (e) { console.error('doPreview:', e); }
    }

    /* ─── Requirement checker ────────────────────────────────────────────── */
    function meetsReq(code, req) {
        var lc  = code.toLowerCase();
        var rlc = req.toLowerCase();
        /* quoted literal */
        var qm = req.match(/['"`]([^'"`]{2,30}?)['"`]/g);
        if (qm) for (var qi = 0; qi < qm.length; qi++) {
            if (lc.indexOf(qm[qi].slice(1, -1).toLowerCase()) !== -1) return true;
        }
        /* HTML tags named in requirement */
        var tagRe = /\b(html|head|body|div|span|p\b|h[1-6]|a\b|img|ul|ol|li|nav|header|footer|main|section|article|aside|form|input|button|select|textarea|table|tr|td|th|label|fieldset|legend|figure|figcaption|canvas|svg|video|audio|source|strong|em|code|pre|blockquote|meta|title|link|script|style)\b/g;
        var tags = rlc.match(tagRe);
        if (tags) {
            var uniq = tags.filter(function (t, i, a) { return a.indexOf(t) === i; });
            return uniq.every(function (t) { return lc.indexOf('<' + t) !== -1; });
        }
        /* CSS properties */
        var css = ['display','flex','grid','color','background','margin','padding','border',
                   'font-size','font-weight','width','height','position','transform',
                   'transition','animation','opacity','gap','align-items','justify-content',
                   '@keyframes','@media','clamp','var(--'];
        for (var ci = 0; ci < css.length; ci++) {
            if (rlc.indexOf(css[ci]) !== -1 && lc.indexOf(css[ci]) !== -1) return true;
        }
        /* JS keywords */
        var js = ['function','const','let','var','return','if','else','for','while','class',
                  'new','async','await','fetch','addeventlistener','queryselector',
                  'getelementbyid','innerhtml','textcontent','classlist','promise','settimeout'];
        for (var ji = 0; ji < js.length; ji++) {
            if (rlc.indexOf(js[ji]) !== -1 && lc.indexOf(js[ji]) !== -1) return true;
        }
        /* numeric count: "3 paragraphs" */
        var nm = rlc.match(/(\d+)\s+(paragraph|heading|link|list item|input|button)/);
        if (nm) {
            var n = parseInt(nm[1]);
            var map = { paragraph:'<p', heading:'<h', link:'<a', 'list item':'<li', input:'<input', button:'<button' };
            var ndl = (map[nm[2]] || ('<' + nm[2])).replace(/</g, '\\<');
            var cnt = (lc.match(new RegExp(ndl, 'g')) || []).length;
            return cnt >= n;
        }
        return lc.replace(/\s/g, '').length > 40;
    }

    /* ─── Feedback renderer ──────────────────────────────────────────────── */
    function renderFeedback(el, allMet, results) {
        if (!el) return;
        if (!results.length) {
            el.innerHTML = '<div class="ex-fb ex-fb-info"><i class="bi bi-eye me-2"></i>Preview updated — inspect the result on the right.</div>';
            el.style.display = 'block';
            return;
        }
        var passed = results.filter(function (r) { return r.met; }).length;
        var tone   = allMet ? 'success' : (passed > 0 ? 'warning' : 'danger');
        var ico    = allMet ? 'check-circle-fill' : (passed > 0 ? 'exclamation-circle-fill' : 'x-circle-fill');
        var head   = allMet
            ? 'All requirements met — great work!'
            : (passed + ' of ' + results.length + ' requirement' + (results.length > 1 ? 's' : '') + ' completed.');
        var h = '<div class="ex-fb ex-fb-' + tone + '"><div class="ex-fb-head"><i class="bi bi-' + ico + ' me-2"></i>' + head + '</div><ul class="ex-fb-list">';
        results.forEach(function (r) {
            h += '<li><i class="bi bi-' + (r.met ? 'check-circle-fill text-success' : 'circle text-muted') + ' me-2"></i>' + r.text + '</li>';
        });
        h += '</ul></div>';
        el.innerHTML = h;
        el.style.display = 'block';
    }

    /* ─── Convenience getters ────────────────────────────────────────────── */
    function getCode(id) {
        var ed = window['monacoEditor_' + id];
        if (ed) return ed.getValue();
        var ta = document.getElementById('code-textarea-' + id);
        return ta ? ta.value : '';
    }
    function getType(id) {
        var ta = document.getElementById('code-textarea-' + id);
        return ta ? (ta.getAttribute('data-exercise-type') || 'html') : 'html';
    }
    function getEl(id)     { return document.querySelector('[data-exercise-id="' + id + '"]'); }
    function getIframe(id) { var e = getEl(id); return e ? e.querySelector('.code-preview-iframe') : null; }
    function getFb(id)     { var e = getEl(id); return e ? e.querySelector('.exercise-feedback')   : null; }

    /* ─── Button handlers ────────────────────────────────────────────────── */
    window.runExercise = function (id) {
        try {
            var iframe = getIframe(id);
            if (iframe) doPreview(getCode(id), getType(id), iframe);
            var fb = getFb(id);
            if (fb) { fb.innerHTML = ''; fb.style.display = 'none'; }
        } catch (e) { console.error('runExercise:', e); }
    };

    window.submitExercise = function (id) {
        try {
            window.runExercise(id);
            var code = getCode(id);
            var el   = getEl(id);
            var fb   = getFb(id);
            var ins  = el ? el.querySelector('.exercise-instructions') : null;
            if (!ins) { renderFeedback(fb, true, []); return; }
            var reqs = (ins.innerText || '').split('\n')
                .map(function (l) { return l.trim(); })
                .filter(function (l) { return /^\d+\./.test(l); })
                .map(function (l) { return l.replace(/^\d+\.\s*/, ''); });
            if (!reqs.length) { renderFeedback(fb, true, []); return; }
            var results = reqs.map(function (r) { return { text: r, met: meetsReq(code, r) }; });
            renderFeedback(fb, results.every(function (r) { return r.met; }), results);
        } catch (e) { console.error('submitExercise:', e); }
    };

    window.resetExercise = function (id) {
        try {
            if (!confirm('Reset to starter code? Your current changes will be lost.')) return;
            var ta      = document.getElementById('code-textarea-' + id);
            var starter = ta ? (ta.dataset.starterCode || '') : '';
            var ed      = window['monacoEditor_' + id];
            if (ed) ed.setValue(starter);
            if (ta)  ta.value = starter;
            var iframe = getIframe(id);
            if (iframe) doPreview(starter, getType(id), iframe);
            var fb = getFb(id);
            if (fb) { fb.innerHTML = ''; fb.style.display = 'none'; }
        } catch (e) { console.error('resetExercise:', e); }
    };

    /* ─── Monaco init (async — editors load after buttons are wired up) ──── */
    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        monaco.editor.defineTheme('hackathonDark', {
            base: 'vs-dark', inherit: true, rules: [],
            colors: {
                'editor.background':             '#0D1117',
                'editor.foreground':             '#E0E0E0',
                'editorCursor.foreground':       '#F8B526',
                'editor.lineHighlightBackground':'#1C233333',
                'editor.selectionBackground':    '#F8B52633'
            }
        });

        document.querySelectorAll('.monaco-editor-mount').forEach(function (mountEl) {
            var exerciseId = mountEl.dataset.exerciseId;
            var lang       = mountEl.dataset.lang || 'html';
            var textarea   = document.getElementById('code-textarea-' + exerciseId);
            if (!textarea) return;

            var editor = monaco.editor.create(mountEl, {
                value:                textarea.value,
                language:             lang,
                theme:                'hackathonDark',
                minimap:              { enabled: false },
                fontSize:             14,
                fontFamily:           "'JetBrains Mono', 'Fira Code', monospace",
                lineNumbers:          'on',
                scrollBeyondLastLine: false,
                automaticLayout:      true,
                tabSize:              2,
                wordWrap:             'on',
                padding:              { top: 12 }
            });

            window['monacoEditor_' + exerciseId] = editor;

            /* initial preview */
            var type   = textarea.getAttribute('data-exercise-type') || 'html';
            var iframe = mountEl.closest('.code-exercise').querySelector('.code-preview-iframe');
            if (iframe) doPreview(textarea.value, type, iframe);

            /* live auto-preview, debounced 350 ms */
            var timer;
            editor.onDidChangeModelContent(function () {
                textarea.value = editor.getValue();
                clearTimeout(timer);
                timer = setTimeout(function () {
                    if (iframe) doPreview(editor.getValue(), type, iframe);
                }, 350);
            });
        });
    });
}());
</script>
<?php
}
