<?php
$pageTitle = 'Final Exam';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$exam = get_qualifying_exam();

// Save exam settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exam'])) {
    verify_csrf();
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $passMark     = max(1, min(100, (int)($_POST['pass_mark'] ?? 70)));
    $timeLimit    = max(10, min(300, (int)($_POST['time_limit'] ?? 90)));
    $isActive     = isset($_POST['is_active']) ? 1 : 0;

    if ($exam) {
        db()->prepare('UPDATE qualifying_exam SET title=?, description=?, instructions=?, pass_mark=?, time_limit=?, is_active=? WHERE id=?')
            ->execute([$title, $description, $instructions, $passMark, $timeLimit, $isActive, $exam['id']]);
    } else {
        db()->prepare('INSERT INTO qualifying_exam (title, description, instructions, pass_mark, time_limit, is_active) VALUES (?,?,?,?,?,?)')
            ->execute([$title, $description, $instructions, $passMark, $timeLimit, $isActive]);
    }
    set_flash('success', 'Exam settings saved.');
    header('Location: /admin/qualifying_exam.php');
    exit;
}

// Save question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    verify_csrf();
    $exam = get_qualifying_exam();
    if (!$exam) { set_flash('error', 'Create exam settings first.'); header('Location: /admin/qualifying_exam.php'); exit; }

    $qid      = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    $text     = trim($_POST['question_text'] ?? '');
    $opts     = array_map('trim', $_POST['options'] ?? []);
    $correct  = (int)($_POST['correct'] ?? 0);
    $points   = max(1, (int)($_POST['points'] ?? 1));
    $tag      = trim($_POST['course_tag'] ?? '');

    if (strlen($text) < 5 || count(array_filter($opts)) < 2) {
        set_flash('error', 'Question text and at least 2 options are required.');
        header('Location: /admin/qualifying_exam.php');
        exit;
    }

    $optsJson = json_encode(array_values($opts));

    if ($qid) {
        db()->prepare('UPDATE qualifying_questions SET course_tag=?, question_text=?, options_json=?, correct_answer=?, points=? WHERE id=?')
            ->execute([$tag ?: null, $text, $optsJson, (string)$correct, $points, $qid]);
        set_flash('success', 'Question updated.');
    } else {
        $maxOrder = (int)db()->query('SELECT COALESCE(MAX(order_index),0)+1 FROM qualifying_questions WHERE exam_id=' . $exam['id'])->fetchColumn();
        db()->prepare('INSERT INTO qualifying_questions (exam_id, course_tag, question_text, options_json, correct_answer, points, order_index) VALUES (?,?,?,?,?,?,?)')
            ->execute([$exam['id'], $tag ?: null, $text, $optsJson, (string)$correct, $points, $maxOrder]);
        set_flash('success', 'Question added.');
    }
    header('Location: /admin/qualifying_exam.php');
    exit;
}

// Delete question
if (($_GET['action'] ?? '') === 'delete_question') {
    start_session();
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) { die('Invalid token.'); }
    $qid = filter_input(INPUT_GET, 'qid', FILTER_VALIDATE_INT);
    if ($qid) {
        db()->prepare('DELETE FROM qualifying_questions WHERE id = ?')->execute([$qid]);
        set_flash('success', 'Question deleted.');
    }
    header('Location: /admin/qualifying_exam.php');
    exit;
}

// Load edit question if requested
$editQ = null;
if (($_GET['action'] ?? '') === 'edit_question') {
    $qid = filter_input(INPUT_GET, 'qid', FILTER_VALIDATE_INT);
    if ($qid) {
        $s = db()->prepare('SELECT * FROM qualifying_questions WHERE id = ?');
        $s->execute([$qid]);
        $editQ = $s->fetch() ?: null;
        if ($editQ) $editQ['options'] = json_decode($editQ['options_json'], true) ?? [];
    }
}

$exam = get_qualifying_exam();
$questions = $exam ? get_qualifying_questions($exam['id']) : [];
$courses = get_all_published_courses();

// Stats
$totalAttempts = $exam ? (int)db()->query('SELECT COUNT(*) FROM qualifying_attempts WHERE exam_id=' . $exam['id'] . ' AND completed_at IS NOT NULL')->fetchColumn() : 0;
$passedCount   = $exam ? (int)db()->query('SELECT COUNT(*) FROM qualifying_attempts WHERE exam_id=' . $exam['id'] . ' AND passed=1')->fetchColumn() : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <div class="admin-content">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Final Exam</h1>
            <?php if ($exam): ?>
            <div class="d-flex gap-2">
                <span class="badge-chip <?= $exam['is_active'] ? 'chip-published' : 'chip-draft' ?>"><?= $exam['is_active'] ? 'Active' : 'Inactive' ?></span>
                <a href="/pages/qualifying_exam.php" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye me-1"></i>Preview</a>
                <a href="/admin/proctor_images.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-camera me-1"></i>Proctor Images</a>
            </div>
            <?php endif; ?>
        </div>

        <?php render_flash(); ?>

        <?php if ($exam): ?>
        <!-- Stats row -->
        <div class="row g-3 mb-4">
            <div class="col-sm-3">
                <div class="stat-card"><div class="stat-icon blue"><i class="bi bi-question-circle"></i></div><div><div class="stat-value"><?= count($questions) ?></div><div class="stat-label">Questions</div></div></div>
            </div>
            <div class="col-sm-3">
                <div class="stat-card"><div class="stat-icon purple"><i class="bi bi-people"></i></div><div><div class="stat-value"><?= $totalAttempts ?></div><div class="stat-label">Attempts</div></div></div>
            </div>
            <div class="col-sm-3">
                <div class="stat-card"><div class="stat-icon green"><i class="bi bi-check-circle"></i></div><div><div class="stat-value"><?= $passedCount ?></div><div class="stat-label">Passed</div></div></div>
            </div>
            <div class="col-sm-3">
                <div class="stat-card"><div class="stat-icon orange"><i class="bi bi-percent"></i></div><div><div class="stat-value"><?= $totalAttempts > 0 ? round($passedCount/$totalAttempts*100) : 0 ?>%</div><div class="stat-label">Pass Rate</div></div></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left: settings + add question -->
            <div class="col-lg-4">
                <div class="card mb-4">
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
                            <button class="btn btn-primary btn-sm w-100">Save Settings</button>
                        </form>
                    </div>
                </div>

                <?php if ($exam): ?>
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3"><?= $editQ ? 'Edit Question' : 'Add Question' ?></h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_question" value="1">
                            <?php if ($editQ): ?>
                            <input type="hidden" name="question_id" value="<?= $editQ['id'] ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Course Tag <span class="text-muted small">(optional)</span></label>
                                <select name="course_tag" class="form-select form-select-sm">
                                    <option value="">— None —</option>
                                    <?php foreach ($courses as $c): ?>
                                    <option value="<?= h($c['title']) ?>" <?= ($editQ['course_tag'] ?? '') === $c['title'] ? 'selected' : '' ?>><?= h($c['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Question</label>
                                <textarea name="question_text" class="form-control" rows="3" required><?= h($editQ['question_text'] ?? '') ?></textarea>
                            </div>
                            <label class="form-label d-flex justify-content-between">
                                Options <span class="text-muted small">Select correct answer</span>
                            </label>
                            <?php $editOpts = $editQ['options'] ?? ['','','','']; ?>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <input type="radio" name="correct" value="<?= $i ?>"
                                    <?= ($editQ ? (string)$editQ['correct_answer'] === (string)$i : $i === 0) ? 'checked' : '' ?>
                                    title="Mark as correct answer">
                                <input type="text" name="options[<?= $i ?>]" class="form-control form-control-sm"
                                    placeholder="Option <?= chr(65+$i) ?>"
                                    value="<?= h($editOpts[$i] ?? '') ?>" required>
                            </div>
                            <?php endfor; ?>
                            <div class="mb-3 mt-1">
                                <label class="form-label">Points</label>
                                <input type="number" name="points" class="form-control form-control-sm" min="1" max="10" value="<?= $editQ['points'] ?? 1 ?>">
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm flex-grow-1"><?= $editQ ? 'Update' : 'Add Question' ?></button>
                                <?php if ($editQ): ?>
                                <a href="/admin/qualifying_exam.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: questions list -->
            <div class="col-lg-8">
                <h5 class="fw-700 mb-3">Questions (<?= count($questions) ?>)</h5>
                <?php if (!$exam): ?>
                <div class="alert alert-info">Save exam settings first to start adding questions.</div>
                <?php elseif (empty($questions)): ?>
                <div class="card"><div class="card-body text-center text-muted py-5">No questions yet. Add some on the left.</div></div>
                <?php endif; ?>

                <?php foreach ($questions as $qi => $q): ?>
                <?php $opts = json_decode($q['options_json'], true) ?? []; ?>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div>
                                <?php if ($q['course_tag']): ?>
                                <span class="badge" style="background:var(--primary-glow);color:var(--primary);font-size:.65rem"><?= h($q['course_tag']) ?></span>
                                <?php endif; ?>
                                <div class="fw-600 small mt-1">Q<?= $qi+1 ?>. <?= h($q['question_text']) ?></div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="/admin/qualifying_exam.php?action=edit_question&qid=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="/admin/qualifying_exam.php?action=delete_question&qid=<?= $q['id'] ?>&csrf_token=<?= csrf_token() ?>"
                                   class="btn btn-sm btn-outline-danger" data-confirm="Delete this question?"><i class="bi bi-trash"></i></a>
                            </div>
                        </div>
                        <div class="row g-1">
                            <?php foreach ($opts as $idx => $opt): ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 small p-1 rounded <?= (string)$idx === (string)$q['correct_answer'] ? 'bg-success bg-opacity-10' : '' ?>">
                                    <i class="bi bi-<?= (string)$idx === (string)$q['correct_answer'] ? 'check-circle-fill text-success' : 'circle text-muted' ?>" style="font-size:.7rem;flex-shrink:0"></i>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
