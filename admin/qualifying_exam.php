<?php
$pageTitle = 'Final Exam';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$exam = get_qualifying_exam();

// ── Save exam settings ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exam'])) {
    verify_csrf();
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $passMark     = max(1, min(100, (int)($_POST['pass_mark'] ?? 70)));
    $timeLimit    = max(10, min(300, (int)($_POST['time_limit'] ?? 90)));
    $isActive     = isset($_POST['is_active']) ? 1 : 0;

    if ($exam) {
        db()->prepare('UPDATE qualifying_exam SET title=?,description=?,instructions=?,pass_mark=?,time_limit=?,is_active=? WHERE id=?')
            ->execute([$title,$description,$instructions,$passMark,$timeLimit,$isActive,$exam['id']]);
    } else {
        db()->prepare('INSERT INTO qualifying_exam (title,description,instructions,pass_mark,time_limit,is_active) VALUES (?,?,?,?,?,?)')
            ->execute([$title,$description,$instructions,$passMark,$timeLimit,$isActive]);
    }
    set_flash('success', 'Exam settings saved.');
    header('Location: /admin/qualifying_exam.php'); exit;
}

// ── Add question ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    verify_csrf();
    $exam = get_qualifying_exam();
    if (!$exam) { set_flash('error', 'Save exam settings first.'); header('Location: /admin/qualifying_exam.php'); exit; }

    $text    = trim($_POST['question_text'] ?? '');
    $opts    = array_map('trim', $_POST['options'] ?? []);
    $correct = (int)($_POST['correct'] ?? 0);
    $points  = max(1, (int)($_POST['points'] ?? 1));
    $tag     = trim($_POST['course_tag'] ?? '');

    if (strlen($text) < 5 || count(array_filter($opts)) < 2) {
        set_flash('error', 'Question text and at least 2 options are required.');
        header('Location: /admin/qualifying_exam.php'); exit;
    }

    $maxOrder = (int)db()->query('SELECT COALESCE(MAX(order_index),0)+1 FROM qualifying_questions WHERE exam_id='.$exam['id'])->fetchColumn();
    db()->prepare('INSERT INTO qualifying_questions (exam_id,course_tag,question_text,options_json,correct_answer,points,order_index) VALUES (?,?,?,?,?,?,?)')
        ->execute([$exam['id'], $tag ?: null, $text, json_encode(array_values($opts)), (string)$correct, $points, $maxOrder]);
    set_flash('success', 'Question added.');
    header('Location: /admin/qualifying_exam.php'); exit;
}

// ── Edit question ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_question'])) {
    verify_csrf();
    $qid     = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    $text    = trim($_POST['question_text'] ?? '');
    $opts    = array_map('trim', $_POST['options'] ?? []);
    $correct = (int)($_POST['correct'] ?? 0);
    $points  = max(1, (int)($_POST['points'] ?? 1));
    $tag     = trim($_POST['course_tag'] ?? '');

    if ($qid && strlen($text) >= 5 && count(array_filter($opts)) >= 2) {
        db()->prepare('UPDATE qualifying_questions SET course_tag=?,question_text=?,options_json=?,correct_answer=?,points=? WHERE id=?')
            ->execute([$tag ?: null, $text, json_encode(array_values($opts)), (string)$correct, $points, $qid]);
        set_flash('success', 'Question updated.');
    } else {
        set_flash('error', 'Please fill in all required fields.');
    }
    header('Location: /admin/qualifying_exam.php'); exit;
}

// ── Delete question ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    verify_csrf();
    $qid = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    if ($qid) {
        db()->prepare('DELETE FROM qualifying_questions WHERE id=?')->execute([$qid]);
        set_flash('success', 'Question deleted.');
    }
    header('Location: /admin/qualifying_exam.php'); exit;
}

$exam      = get_qualifying_exam();
$questions = $exam ? get_qualifying_questions($exam['id']) : [];
$courses   = get_all_published_courses();

$totalAttempts = $exam ? (int)db()->query('SELECT COUNT(*) FROM qualifying_attempts WHERE exam_id='.$exam['id'].' AND completed_at IS NOT NULL')->fetchColumn() : 0;
$passedCount   = $exam ? (int)db()->query('SELECT COUNT(*) FROM qualifying_attempts WHERE exam_id='.$exam['id'].' AND passed=1')->fetchColumn() : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-content">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <h1 class="admin-page-title mb-0">Final Exam</h1>
            <?php if ($exam): ?>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge-chip <?= $exam['is_active'] ? 'chip-published' : 'chip-draft' ?>"><?= $exam['is_active'] ? 'Active' : 'Inactive' ?></span>
                <a href="/pages/qualifying_exam.php" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1"></i>Preview</a>
                <a href="/admin/proctor_images.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-camera me-1"></i>Proctor Images</a>
                <?php if ($exam): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Question
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php render_flash(); ?>

        <?php if ($exam): ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-sm-3"><div class="stat-card"><div class="stat-icon blue"><i class="bi bi-question-circle"></i></div><div><div class="stat-value"><?= count($questions) ?></div><div class="stat-label">Questions</div></div></div></div>
            <div class="col-6 col-sm-3"><div class="stat-card"><div class="stat-icon purple"><i class="bi bi-people"></i></div><div><div class="stat-value"><?= $totalAttempts ?></div><div class="stat-label">Attempts</div></div></div></div>
            <div class="col-6 col-sm-3"><div class="stat-card"><div class="stat-icon green"><i class="bi bi-check-circle"></i></div><div><div class="stat-value"><?= $passedCount ?></div><div class="stat-label">Passed</div></div></div></div>
            <div class="col-6 col-sm-3"><div class="stat-card"><div class="stat-icon orange"><i class="bi bi-percent"></i></div><div><div class="stat-value"><?= $totalAttempts > 0 ? round($passedCount/$totalAttempts*100) : 0 ?>%</div><div class="stat-label">Pass Rate</div></div></div></div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Exam settings -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">Exam Settings</h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_exam" value="1">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= h($exam['title'] ?? 'Final Exam') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= h($exam['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions <span class="text-muted small">(shown to candidates)</span></label>
                                <textarea name="instructions" class="form-control" rows="3"><?= h($exam['instructions'] ?? '') ?></textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Pass Mark (%)</label>
                                    <input type="number" name="pass_mark" class="form-control" min="1" max="100" value="<?= $exam['pass_mark'] ?? 70 ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Time Limit (min)</label>
                                    <input type="number" name="time_limit" class="form-control" min="10" max="300" value="<?= $exam['time_limit'] ?? 90 ?>">
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" <?= ($exam['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label for="is_active" class="form-check-label">Active (visible to candidates)</label>
                            </div>
                            <button class="btn btn-primary w-100">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Questions list -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-700 mb-0">Questions (<?= count($questions) ?>)</h5>
                    <?php if ($exam): ?>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Question
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (!$exam): ?>
                <div class="alert alert-info">Save exam settings first to start adding questions.</div>
                <?php elseif (empty($questions)): ?>
                <div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-question-circle d-block mb-2" style="font-size:2rem;opacity:.4"></i>No questions yet. Click "Add Question" to get started.</div></div>
                <?php endif; ?>

                <?php foreach ($questions as $qi => $q): ?>
                <?php $opts = json_decode($q['options_json'], true) ?? []; ?>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="flex-grow-1">
                                <?php if ($q['course_tag']): ?>
                                <span class="badge mb-1" style="background:var(--primary-glow);color:var(--primary);font-size:.65rem"><?= h($q['course_tag']) ?></span>
                                <?php endif; ?>
                                <div class="fw-600 small">Q<?= $qi+1 ?>. <?= h($q['question_text']) ?></div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="openEdit(<?= htmlspecialchars(json_encode([
                                        'id'      => $q['id'],
                                        'tag'     => $q['course_tag'],
                                        'text'    => $q['question_text'],
                                        'options' => $opts,
                                        'correct' => $q['correct_answer'],
                                        'points'  => $q['points'],
                                    ]), ENT_QUOTES) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete(<?= $q['id'] ?>, <?= htmlspecialchars(json_encode('Q'.($qi+1).': '.mb_substr($q['question_text'],0,60)), ENT_QUOTES) ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-1">
                            <?php foreach ($opts as $idx => $opt): ?>
                            <?php $isCorrect = (string)$idx === (string)$q['correct_answer']; ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 small p-1 rounded <?= $isCorrect ? 'bg-success bg-opacity-10' : '' ?>">
                                    <i class="bi bi-<?= $isCorrect ? 'check-circle-fill text-success' : 'circle text-muted' ?>" style="font-size:.7rem;flex-shrink:0"></i>
                                    <?= h($opt) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-1 text-end"><span class="text-muted small"><?= $q['points'] ?> pt<?= $q['points'] != 1 ? 's' : '' ?></span></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Add Question Modal ───────────────────────────────────────────────── -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="add_question" value="1">
                <div class="modal-body">
                    <?php echo questionFormFields($courses, null); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Edit Question Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Edit Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="edit_question" value="1">
                <input type="hidden" name="question_id" id="edit_question_id">
                <div class="modal-body" id="editModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete Question Form (hidden) ───────────────────────────────────── -->
<form method="POST" id="deleteForm" style="display:none">
    <?= csrf_field() ?>
    <input type="hidden" name="delete_question" value="1">
    <input type="hidden" name="question_id" id="delete_question_id">
</form>

<?php
function questionFormFields(array $courses, ?array $q): string {
    $opts = $q ? (json_decode($q['options_json'], true) ?? ['','','','']) : ['','','',''];
    while (count($opts) < 4) $opts[] = '';
    $html = '';

    // Course tag
    $html .= '<div class="mb-3"><label class="form-label">Course Tag <span class="text-muted small">(optional)</span></label>';
    $html .= '<select name="course_tag" class="form-select"><option value="">— None —</option>';
    foreach ($courses as $c) {
        $sel = ($q && $q['course_tag'] === $c['title']) ? ' selected' : '';
        $html .= '<option value="'.h($c['title']).'"'.$sel.'>'.h($c['title']).'</option>';
    }
    $html .= '</select></div>';

    // Question text
    $html .= '<div class="mb-3"><label class="form-label">Question <span class="text-danger">*</span></label>';
    $html .= '<textarea name="question_text" class="form-control" rows="3" required>'.h($q['question_text'] ?? '').'</textarea></div>';

    // Options
    $html .= '<label class="form-label d-flex justify-content-between">Options <span class="text-muted small">Select the correct answer</span></label>';
    for ($i = 0; $i < 4; $i++) {
        $checked = ($q && (string)$q['correct_answer'] === (string)$i) ? ' checked' : ($i === 0 && !$q ? ' checked' : '');
        $html .= '<div class="d-flex align-items-center gap-2 mb-2">';
        $html .= '<input type="radio" name="correct" value="'.$i.'" title="Mark as correct"'.$checked.' required>';
        $html .= '<input type="text" name="options['.$i.']" class="form-control" placeholder="Option '.chr(65+$i).'" value="'.h($opts[$i] ?? '').'" required>';
        $html .= '</div>';
    }

    // Points
    $html .= '<div class="mt-3" style="max-width:120px"><label class="form-label">Points</label>';
    $html .= '<input type="number" name="points" class="form-control" min="1" max="10" value="'.($q['points'] ?? 1).'"></div>';

    return $html;
}
?>

<script>
const courses = <?= json_encode(array_column($courses, 'title')) ?>;

function openEdit(q) {
    document.getElementById('edit_question_id').value = q.id;

    // Build form HTML matching the PHP helper
    let html = `
    <div class="mb-3">
        <label class="form-label">Course Tag <span class="text-muted small">(optional)</span></label>
        <select name="course_tag" class="form-select">
            <option value="">— None —</option>
            ${courses.map(c => `<option value="${escHtml(c)}" ${q.tag === c ? 'selected' : ''}>${escHtml(c)}</option>`).join('')}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Question <span class="text-danger">*</span></label>
        <textarea name="question_text" class="form-control" rows="3" required>${escHtml(q.text)}</textarea>
    </div>
    <label class="form-label d-flex justify-content-between">Options <span class="text-muted small">Select the correct answer</span></label>`;

    const letters = ['A','B','C','D'];
    const opts = q.options.length >= 4 ? q.options : [...q.options, ...Array(4-q.options.length).fill('')];
    opts.slice(0,4).forEach((opt, i) => {
        html += `<div class="d-flex align-items-center gap-2 mb-2">
            <input type="radio" name="correct" value="${i}" ${String(q.correct) === String(i) ? 'checked' : ''} required>
            <input type="text" name="options[${i}]" class="form-control" placeholder="Option ${letters[i]}" value="${escHtml(opt)}" required>
        </div>`;
    });

    html += `<div class="mt-3" style="max-width:120px">
        <label class="form-label">Points</label>
        <input type="number" name="points" class="form-control" min="1" max="10" value="${q.points}">
    </div>`;

    document.getElementById('editModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDelete(id, preview) {
    if (confirm('Delete this question?\n\n' + preview)) {
        document.getElementById('delete_question_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
