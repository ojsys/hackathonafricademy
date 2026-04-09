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
        <div class="small text-muted">
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
                 style="height: 300px; width: 100%;"></div>
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
    
    echo '<div class="mt-5">';
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

// Override functions to use Monaco
window.resetExercise = function(id) {
    const editor = window['monacoEditor_' + id];
    const textarea = document.getElementById('code-textarea-' + id);
    if (editor) {
        editor.setValue(textarea.dataset.starterCode || '');
    }
};

window.runExercise = function(id) {
    const editor = window['monacoEditor_' + id];
    if (!editor) return;
    const code = editor.getValue();
    const exerciseEl = document.querySelector('[data-exercise-id="' + id + '"]');
    const iframe = exerciseEl.querySelector('.code-preview-iframe');
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open(); doc.write(code); doc.close();
};

window.submitExercise = function(id) {
    runExercise(id);
    const feedback = document.querySelector('[data-exercise-id="' + id + '"] .exercise-feedback');
    if (feedback) {
        feedback.innerHTML = '<div class="p-3" style="background:var(--primary-glow);border-radius:var(--radius);"><i class="bi bi-check-circle text-success me-2"></i>Code submitted! Preview updated.</div>';
        feedback.style.display = 'block';
    }
};
</script>
<?php
}
