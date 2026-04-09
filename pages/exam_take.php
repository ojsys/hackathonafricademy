<?php
$pageTitle = 'Taking Exam';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$examId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$examId) { header('Location: /pages/courses.php'); exit; }

$exam = null;
$stmt = db()->prepare('SELECT fe.*, c.title as course_title, c.id as course_id FROM final_exams fe JOIN courses c ON c.id = fe.course_id WHERE fe.id = ? AND fe.is_active = 1');
$stmt->execute([$examId]);
$exam = $stmt->fetch();
if (!$exam) { header('Location: /pages/courses.php'); exit; }

// Must have completed the course
if (!is_course_complete($user['id'], $exam['course_id'])) {
    set_flash('warning', 'Complete all lessons and quizzes before taking the exam.');
    header('Location: /pages/course.php?id=' . $exam['course_id']);
    exit;
}

// Check if already passed
$bestAttempt = get_best_exam_attempt($user['id'], $examId);
if ($bestAttempt && $bestAttempt['passed']) {
    header('Location: /pages/final_exam.php?course_id=' . $exam['course_id']);
    exit;
}

$questions = get_final_exam_questions($examId);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.exam-timer { 
    position: sticky; top: 0; z-index: 100; 
    background: var(--surface); border-bottom: 1px solid var(--border); 
    padding: 0.75rem 0; 
}
.exam-timer .timer-display { 
    font-family: var(--font-mono); font-size: 1.25rem; font-weight: 700; 
}
.exam-timer .timer-display.warning { color: var(--warning); }
.exam-timer .timer-display.danger { color: var(--danger); animation: blink 1s infinite; }
.exam-question { 
    background: var(--surface); border: 1px solid var(--border); 
    border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1.5rem; 
}
.exam-question.coding { border-left: 3px solid var(--primary); }
.exam-question.mcq { border-left: 3px solid var(--warning); }
.exam-option { 
    display: flex; align-items: center; gap: 0.75rem; 
    padding: 0.75rem 1rem; background: var(--bg); 
    border: 1px solid var(--border); border-radius: var(--radius); 
    cursor: pointer; transition: border-color 0.2s, background 0.2s; margin-bottom: 0.5rem; 
}
.exam-option:hover { border-color: var(--primary); background: var(--primary-glow); }
.exam-option input[type="radio"] { accent-color: var(--primary); }
.exam-option input[type="radio"]:checked + span { color: var(--primary); font-weight: 600; }
.exam-code-editor { 
    width: 100%; min-height: 200px; padding: 1rem; 
    background: #1a1a2e; color: #e0e0e0; border: 1px solid var(--border); 
    border-radius: var(--radius); font-family: var(--font-mono); 
    font-size: 0.875rem; resize: vertical; tab-size: 4; 
}
.question-nav { 
    display: flex; flex-wrap: wrap; gap: 0.5rem; 
}
.question-nav .q-dot { 
    width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; 
    border-radius: 50%; background: var(--bg); border: 1px solid var(--border); 
    font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; 
}
.question-nav .q-dot.answered { background: var(--primary-glow); border-color: var(--primary); color: var(--primary); }
.question-nav .q-dot:hover { border-color: var(--primary); }
</style>

<div class="exam-timer" data-testid="exam-timer">
    <div class="container d-flex align-items-center justify-content-between">
        <div>
            <strong><?= h($exam['course_title']) ?></strong> — Final Exam
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="question-nav" data-testid="question-nav">
                <?php for ($i = 1; $i <= count($questions); $i++): ?>
                <span class="q-dot" data-q="<?= $i ?>" onclick="scrollToQuestion(<?= $i ?>)"><?= $i ?></span>
                <?php endfor; ?>
            </div>
            <div class="timer-display" data-testid="timer-display" id="examTimer">
                <?= $exam['time_limit'] ?>:00
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form id="examForm" action="/actions/submit_exam.php" method="POST" data-testid="exam-form">
                <?= csrf_field() ?>
                <input type="hidden" name="exam_id" value="<?= $exam['id'] ?>">
                <input type="hidden" name="time_taken" id="timeTaken" value="0">

                <?php foreach ($questions as $idx => $q): $num = $idx + 1; ?>
                <div class="exam-question <?= $q['question_type'] ?>" id="question-<?= $num ?>" data-testid="question-<?= $num ?>">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="badge <?= $q['question_type'] === 'mcq' ? 'bg-warning text-dark' : 'bg-primary' ?> mb-2">
                                <?= $q['question_type'] === 'mcq' ? 'Multiple Choice' : 'Coding Challenge' ?>
                            </span>
                            <h5 class="fw-700 mb-0">Question <?= $num ?></h5>
                        </div>
                        <span class="badge bg-secondary"><?= $q['points'] ?> pts</span>
                    </div>

                    <p class="mb-3"><?= nl2br(h($q['question_text'])) ?></p>

                    <?php if ($q['question_type'] === 'mcq'): ?>
                        <?php $options = json_decode($q['options_json'], true) ?? []; ?>
                        <?php foreach ($options as $optIdx => $opt): ?>
                        <label class="exam-option" data-testid="option-<?= $num ?>-<?= $optIdx ?>">
                            <input type="radio" name="answer_<?= $q['id'] ?>" value="<?= $optIdx ?>" onchange="markAnswered(<?= $num ?>)">
                            <span><?= h($opt) ?></span>
                        </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if ($q['starter_code']): ?>
                        <div class="mb-2 small text-muted">Starter code provided. Modify it to complete the challenge:</div>
                        <?php endif; ?>
                        <div id="exam-monaco-<?= $q['id'] ?>"
                             data-question-id="<?= $q['id'] ?>"
                             data-question-num="<?= $num ?>"
                             data-starter="<?= h($q['starter_code'] ?? '') ?>"
                             data-testid="code-answer-<?= $num ?>"
                             style="height: 220px; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;"></div>
                        <textarea name="code_<?= $q['id'] ?>"
                                  id="exam-textarea-<?= $q['id'] ?>"
                                  style="display:none;"
                        ><?= h($q['starter_code'] ?? '') ?></textarea>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
                    <a href="/pages/final_exam.php?course_id=<?= $exam['course_id'] ?>" class="btn btn-outline-secondary" data-testid="cancel-exam">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" data-testid="submit-exam-btn" onclick="return confirm('Are you sure you want to submit? You cannot change your answers after submitting.')">
                        <i class="bi bi-send me-1"></i> Submit Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Timer
const timeLimitMinutes = <?= (int)$exam['time_limit'] ?>;
let secondsLeft = timeLimitMinutes * 60;
const timerEl = document.getElementById('examTimer');
const timeTakenInput = document.getElementById('timeTaken');

const timerInterval = setInterval(() => {
    secondsLeft--;
    const mins = Math.floor(secondsLeft / 60);
    const secs = secondsLeft % 60;
    timerEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
    timeTakenInput.value = (timeLimitMinutes * 60) - secondsLeft;

    if (secondsLeft <= 60) {
        timerEl.classList.add('danger');
        timerEl.classList.remove('warning');
    } else if (secondsLeft <= 300) {
        timerEl.classList.add('warning');
    }

    if (secondsLeft <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! Your exam will be submitted automatically.');
        document.getElementById('examForm').submit();
    }
}, 1000);

function scrollToQuestion(num) {
    document.getElementById('question-' + num).scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function markAnswered(num) {
    const dot = document.querySelector(`.q-dot[data-q="${num}"]`);
    if (dot) dot.classList.add('answered');
}

// Monaco Editor for coding questions
const monacoEditors = {};
</script>
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
<script>
require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
require(['vs/editor/editor.main'], function () {
    monaco.editor.defineTheme('hackathonDark', {
        base: 'vs-dark', inherit: true, rules: [],
        colors: {
            'editor.background': '#0D1117',
            'editor.foreground': '#E0E0E0',
            'editorCursor.foreground': '#F8B526',
            'editor.lineHighlightBackground': '#1C233333',
            'editor.selectionBackground': '#F8B52633'
        }
    });

    document.querySelectorAll('[id^="exam-monaco-"]').forEach(function (el) {
        const qId  = el.dataset.questionId;
        const qNum = parseInt(el.dataset.questionNum);
        const textarea = document.getElementById('exam-textarea-' + qId);

        const editor = monaco.editor.create(el, {
            value: textarea.value,
            language: 'html',
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

        monacoEditors[qId] = editor;

        editor.onDidChangeModelContent(function () {
            textarea.value = editor.getValue();
            markAnswered(qNum);
        });
    });

    // Sync all Monaco editors into their textareas before form submit
    document.getElementById('examForm').addEventListener('submit', function () {
        Object.keys(monacoEditors).forEach(function (qId) {
            document.getElementById('exam-textarea-' + qId).value = monacoEditors[qId].getValue();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
