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
require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' }});
require(['vs/editor/editor.main'], function() {
    // Define dark theme
    monaco.editor.defineTheme('hackathonDark', {
        base: 'vs-dark',
        inherit: true,
        rules: [],
        colors: {
            'editor.background': '#0D1117',
            'editor.foreground': '#E0E0E0',
            'editorCursor.foreground': '#F8B526',
            'editor.lineHighlightBackground': '#1C233333',
            'editor.selectionBackground': '#F8B52633'
        }
    });

    document.querySelectorAll('.monaco-editor-mount').forEach(function(el) {
        const exerciseId = el.dataset.exerciseId;
        const lang = el.dataset.lang || 'html';
        const textarea = document.getElementById('code-textarea-' + exerciseId);
        
        const editor = monaco.editor.create(el, {
            value: textarea.value,
            language: lang,
            theme: 'hackathonDark',
            minimap: { enabled: false },
            fontSize: 14,
            fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
            lineNumbers: 'on',
            scrollBeyondLastLine: false,
            automaticLayout: true,
            tabSize: 2,
            wordWrap: 'on',
            padding: { top: 12 }
        });

        // Sync editor content to hidden textarea
        editor.onDidChangeModelContent(function() {
            textarea.value = editor.getValue();
        });

        // Store editor instance for reset/run functions
        window['monacoEditor_' + exerciseId] = editor;
    });
});

// ── Reset ────────────────────────────────────────────────────────────────
window.resetExercise = function(id) {
    const editor = window['monacoEditor_' + id];
    const textarea = document.getElementById('code-textarea-' + id);
    if (!editor) return;
    if (!confirm('Reset to starter code? Your current changes will be lost.')) return;
    const starter = (textarea && textarea.dataset.starterCode) ? textarea.dataset.starterCode : '';
    editor.setValue(starter);
    if (textarea) textarea.value = starter;
    // Re-render preview with starter code
    const exerciseEl = document.querySelector('[data-exercise-id="' + id + '"]');
    const iframe = exerciseEl.querySelector('.code-preview-iframe');
    const type = textarea ? textarea.getAttribute('data-exercise-type') : 'html';
    renderToPreview(starter, type, iframe);
    const feedback = exerciseEl.querySelector('.exercise-feedback');
    if (feedback) { feedback.innerHTML = ''; feedback.style.display = 'none'; }
};

// ── Run: render code to preview iframe ──────────────────────────────────
window.runExercise = function(id) {
    const editor = window['monacoEditor_' + id];
    if (!editor) return;
    const code = editor.getValue();
    const textarea = document.getElementById('code-textarea-' + id);
    if (textarea) textarea.value = code;  // keep textarea in sync
    const exerciseEl = document.querySelector('[data-exercise-id="' + id + '"]');
    const iframe = exerciseEl.querySelector('.code-preview-iframe');
    const type = textarea ? textarea.getAttribute('data-exercise-type') : 'html';
    renderToPreview(code, type, iframe);
    const feedback = exerciseEl.querySelector('.exercise-feedback');
    if (feedback) { feedback.innerHTML = ''; feedback.style.display = 'none'; }
};

// ── Submit: render + evaluate requirements ───────────────────────────────
window.submitExercise = function(id) {
    runExercise(id);  // show preview first

    const editor = window['monacoEditor_' + id];
    const code = editor ? editor.getValue() : '';
    const exerciseEl = document.querySelector('[data-exercise-id="' + id + '"]');
    const feedback = exerciseEl.querySelector('.exercise-feedback');
    const instrEl = exerciseEl.querySelector('.exercise-instructions');

    if (!instrEl) { showExerciseFeedback(feedback, true, []); return; }

    // Extract numbered requirements from the instructions element
    const requirements = (instrEl.innerText || '')
        .split('\n')
        .map(function(l) { return l.trim(); })
        .filter(function(l) { return /^\d+\./.test(l); })
        .map(function(l) { return l.replace(/^\d+\.\s*/, ''); });

    if (requirements.length === 0) { showExerciseFeedback(feedback, true, []); return; }

    const results = requirements.map(function(req) {
        return { text: req, met: checkRequirement(code, req) };
    });
    const allMet = results.every(function(r) { return r.met; });
    showExerciseFeedback(feedback, allMet, results);
};

// ── Render code into the preview iframe using a Blob URL ─────────────────
function renderToPreview(code, exerciseType, iframe) {
    let html = code;
    // If already a full HTML document, render directly regardless of type
    const isFullDoc = /^\s*(<!DOCTYPE|<html)/i.test(code.trim());

    if (!isFullDoc) {
        if (exerciseType === 'css') {
            html = '<!DOCTYPE html><html><head><style>body{font-family:sans-serif;padding:1.25rem;margin:0}' +
                   code + '</style></head><body>' +
                   '<h1>Heading One</h1><h2>Heading Two</h2>' +
                   '<p>This is a paragraph of sample text.</p>' +
                   '<button class="btn">Button</button> <a href="#">Link</a>' +
                   '<ul><li>List item one</li><li>List item two</li><li>List item three</li></ul>' +
                   '<div class="box card container" style="margin-top:1rem;padding:1rem;border:1px solid #ddd">Sample div / card</div>' +
                   '</body></html>';
        } else if (exerciseType === 'javascript') {
            var escaped = code.replace(/<\/script>/gi, '<\\/script>');
            html = '<!DOCTYPE html><html><head><style>' +
                   'body{font-family:system-ui,sans-serif;padding:1rem;background:#0d1117;color:#e0e0e0;margin:0}' +
                   '#output{background:#161b22;padding:1rem;border-radius:6px;font-family:monospace;white-space:pre-wrap;min-height:4rem;font-size:.9rem}' +
                   '.lbl{font-size:.7rem;color:#8b949e;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}' +
                   '</style></head><body>' +
                   '<div class="lbl">Console output</div><div id="output">…</div><script>' +
                   '(function(){' +
                   'var o=document.getElementById("output");o.textContent="";' +
                   'var cl=console.log,ce=console.error;' +
                   'function fmt(a){return a.map(function(x){return typeof x==="object"?JSON.stringify(x,null,2):String(x);}).join(" ");}' +
                   'console.log=function(){o.textContent+=fmt(Array.from(arguments))+"\\n";cl.apply(console,arguments);};' +
                   'console.error=function(){o.innerHTML+=\'<span style="color:#ff7b72">\'+fmt(Array.from(arguments))+"</span>\\n";ce.apply(console,arguments);};' +
                   'try{' + escaped + '}catch(e){o.innerHTML+=\'<span style="color:#ff7b72">Error: \'+e.message+"</span>";}' +
                   '})();<\/script></body></html>';
        }
    }

    var blob = new Blob([html], { type: 'text/html' });
    iframe.src = URL.createObjectURL(blob);
}

// ── Check one requirement against the submitted code ─────────────────────
function checkRequirement(code, requirement) {
    var lc  = code.toLowerCase();
    var req = requirement.toLowerCase();

    // 1. Quoted literals that should appear verbatim in the code
    var quoted = [];
    requirement.replace(/['"`]([^'"`]{2,}?)['"`]/g, function(_, m) { quoted.push(m.toLowerCase()); });
    if (quoted.length > 0 && quoted.some(function(q) { return lc.includes(q); })) return true;

    // 2. HTML tags mentioned in the requirement
    var tagRe = /\b(html|head|body|div|span|p\b|h[1-6]|a\b|img|ul|ol|li|nav|header|footer|main|section|article|aside|form|input|button|select|textarea|table|tr|td|th|label|fieldset|legend|figure|figcaption|canvas|svg|video|audio|source|strong|em|code|pre|blockquote|br|hr|meta|title|link|script|style|details|summary|template|slot)\b/g;
    var tags = [];
    req.replace(tagRe, function(m) { if (!tags.includes(m)) tags.push(m); });
    if (tags.length > 0) return tags.every(function(tag) { return lc.includes('<' + tag); });

    // 3. CSS properties mentioned
    var cssProps = ['display','flex','grid','color','background','margin','padding','border','font-size',
                    'font-weight','font-family','width','height','position','transform','transition',
                    'animation','opacity','overflow','z-index','cursor','gap','align-items',
                    'justify-content','grid-template','flex-wrap','clamp','var(--','@keyframes',
                    '@media','min-width','max-width'];
    for (var i = 0; i < cssProps.length; i++) {
        if (req.includes(cssProps[i]) && lc.includes(cssProps[i])) return true;
    }

    // 4. JavaScript constructs
    var jsKw = ['function','const','let','var','return','if','else','for','while','class','new',
                'async','await','fetch','addeventlistener','queryselector','getelementbyid',
                'innerhtml','textcontent','classlist','promise','settimeout','setinterval',
                'json.stringify','json.parse','array.from','object.keys','map(','filter(','reduce('];
    for (var j = 0; j < jsKw.length; j++) {
        if (req.includes(jsKw[j]) && lc.includes(jsKw[j])) return true;
    }

    // 5. Exact count checks: "3 paragraphs", "5 list items", etc.
    var countMatch = req.match(/(\d+)\s+(paragraph|heading|link|list item|input|button|column|row|section|div)/);
    if (countMatch) {
        var n = parseInt(countMatch[1]);
        var elemMap = { paragraph:'<p', heading:'<h', link:'<a', 'list item':'<li',
                        input:'<input', button:'<button', section:'<section', div:'<div' };
        var needle = elemMap[countMatch[2]] || '<' + countMatch[2];
        var found = (lc.match(new RegExp(needle.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'), 'g')) || []).length;
        return found >= n;
    }

    // Fallback: code has meaningful content
    return lc.replace(/[\s\n]/g, '').length > 40;
}

// ── Render structured pass/fail feedback ─────────────────────────────────
function showExerciseFeedback(el, allMet, results) {
    if (!el) return;
    if (results.length === 0) {
        el.innerHTML = '<div class="ex-fb ex-fb-info"><i class="bi bi-eye me-2"></i>Preview updated — check the result on the right.</div>';
        el.style.display = 'block';
        return;
    }
    var passed  = results.filter(function(r) { return r.met; }).length;
    var tone    = allMet ? 'success' : (passed > 0 ? 'warning' : 'danger');
    var iconCls = allMet ? 'check-circle-fill' : (passed > 0 ? 'exclamation-circle-fill' : 'x-circle-fill');
    var headline = allMet
        ? 'All requirements met — great work!'
        : passed + ' of ' + results.length + ' requirement' + (results.length > 1 ? 's' : '') + ' completed.';

    var html = '<div class="ex-fb ex-fb-' + tone + '">' +
               '<div class="ex-fb-head"><i class="bi bi-' + iconCls + ' me-2"></i>' + headline + '</div>' +
               '<ul class="ex-fb-list">';
    results.forEach(function(r) {
        html += '<li><i class="bi bi-' + (r.met ? 'check-circle-fill text-success' : 'circle text-muted') + ' me-2"></i>' + r.text + '</li>';
    });
    html += '</ul></div>';
    el.innerHTML = html;
    el.style.display = 'block';
}
</script>
<?php
}
