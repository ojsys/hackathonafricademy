<?php
$pageTitle = 'Manage Quiz';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$moduleId = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);
if (!$moduleId) { header('Location: /admin/courses.php'); exit; }

$stmt = db()->prepare('SELECT m.*, c.id AS course_id, c.title AS course_title FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?');
$stmt->execute([$moduleId]);
$module = $stmt->fetch();
if (!$module) { header('Location: /admin/courses.php'); exit; }

$quiz = get_quiz_for_module($moduleId);

// Save quiz info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
    verify_csrf();
    $title    = trim($_POST['title'] ?? '');
    $passMark = max(1, min(100, (int)($_POST['pass_mark'] ?? 70)));

    if (!$quiz) {
        $stmt = db()->prepare('INSERT INTO quizzes (module_id, title, pass_mark) VALUES (?,?,?)');
        $stmt->execute([$moduleId, $title, $passMark]);
        $quizId = db()->lastInsertId();
    } else {
        $stmt = db()->prepare('UPDATE quizzes SET title=?, pass_mark=? WHERE id=?');
        $stmt->execute([$title, $passMark, $quiz['id']]);
        $quizId = $quiz['id'];
    }
    set_flash('success', 'Quiz settings saved.');
    header('Location: /admin/quizzes.php?module_id=' . $moduleId);
    exit;
}

// Save question + options
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    verify_csrf();
    $qid  = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
    $text = trim($_POST['question_text'] ?? '');
    $opts = $_POST['options'] ?? [];
    $correctIdx = (int)($_POST['correct'] ?? 0);

    if (!$quiz || strlen($text) < 3 || count($opts) < 2) {
        set_flash('error', 'Please fill all fields and provide at least 2 options.');
        header('Location: /admin/quizzes.php?module_id=' . $moduleId);
        exit;
    }

    if ($qid) {
        db()->prepare('UPDATE quiz_questions SET question_text=? WHERE id=?')->execute([$text, $qid]);
        db()->prepare('DELETE FROM quiz_options WHERE question_id=?')->execute([$qid]);
    } else {
        $maxOrder = (int)db()->prepare('SELECT COALESCE(MAX(order_index),0)+1 FROM quiz_questions WHERE quiz_id=?')->execute([$quiz['id']]) ? db()->query('SELECT COALESCE(MAX(order_index),0)+1 FROM quiz_questions WHERE quiz_id=' . $quiz['id'])->fetchColumn() : 1;
        $stmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, order_index) VALUES (?,?,?)');
        $stmt->execute([$quiz['id'], $text, $maxOrder]);
        $qid = db()->lastInsertId();
    }

    foreach ($opts as $i => $optText) {
        $optText = trim($optText);
        if ($optText === '') continue;
        $stmt = db()->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?,?,?)');
        $stmt->execute([$qid, $optText, $i === $correctIdx ? 1 : 0]);
    }

    set_flash('success', 'Question saved.');
    header('Location: /admin/quizzes.php?module_id=' . $moduleId);
    exit;
}

// Delete question
$action = $_GET['action'] ?? '';
$qid    = filter_input(INPUT_GET, 'question_id', FILTER_VALIDATE_INT);
if ($action === 'delete_question' && $qid) {
    verify_csrf_get();
    db()->prepare('DELETE FROM quiz_questions WHERE id=?')->execute([$qid]);
    set_flash('success', 'Question deleted.');
    header('Location: /admin/quizzes.php?module_id=' . $moduleId);
    exit;
}

function verify_csrf_get(): void {
    start_session();
    $token = $_GET['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403); die('Invalid token.');
    }
}

$questions = $quiz ? get_questions_for_quiz($quiz['id']) : [];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/courses.php">Courses</a></li>
                <li class="breadcrumb-item"><a href="/admin/courses.php?action=edit&id=<?= $module['course_id'] ?>"><?= h($module['course_title']) ?></a></li>
                <li class="breadcrumb-item active">Quiz: <?= h($module['title']) ?></li>
            </ol>
        </nav>

        <h1 class="admin-page-title">Quiz Manager — <?= h($module['title']) ?></h1>

        <?php render_flash(); ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <!-- Quiz settings -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">Quiz Settings</h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_quiz" value="1">
                            <div class="mb-3">
                                <label class="form-label">Quiz Title</label>
                                <input type="text" name="title" class="form-control" value="<?= h($quiz['title'] ?? $module['title'] . ' Quiz') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pass Mark (%)</label>
                                <input type="number" name="pass_mark" class="form-control" min="1" max="100" value="<?= $quiz['pass_mark'] ?? 70 ?>">
                            </div>
                            <button class="btn btn-primary btn-sm">Save Settings</button>
                        </form>
                    </div>
                </div>

                <!-- Add question -->
                <?php if ($quiz): ?>
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">Add Question</h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_question" value="1">
                            <div class="mb-3">
                                <label class="form-label">Question</label>
                                <textarea name="question_text" class="form-control" rows="2" required></textarea>
                            </div>
                            <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <input type="radio" name="correct" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> title="Mark as correct">
                                <input type="text" name="options[<?= $i ?>]" class="form-control form-control-sm" placeholder="Option <?= chr(65 + $i) ?>" required>
                            </div>
                            <?php endfor; ?>
                            <small class="text-muted d-block mb-3">Select the radio button next to the correct answer.</small>
                            <button class="btn btn-outline-primary btn-sm">Add Question</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Questions list -->
            <div class="col-lg-8">
                <h5 class="fw-700 mb-3">Questions (<?= count($questions) ?>)</h5>
                <?php if (!$quiz): ?>
                <div class="alert alert-warning">Create and save quiz settings first before adding questions.</div>
                <?php elseif (empty($questions)): ?>
                <div class="empty-state card"><div class="card-body"><i class="bi bi-question-circle text-muted"></i><p>No questions yet. Add some on the left.</p></div></div>
                <?php endif; ?>

                <?php foreach ($questions as $qi => $q): ?>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="fw-600 small">Q<?= $qi + 1 ?>. <?= h($q['question_text']) ?></div>
                            <a href="/admin/quizzes.php?module_id=<?= $moduleId ?>&action=delete_question&question_id=<?= $q['id'] ?>&csrf_token=<?= csrf_token() ?>"
                               class="btn btn-sm btn-outline-danger flex-shrink-0"
                               data-confirm="Delete this question?"><i class="bi bi-trash"></i></a>
                        </div>
                        <div class="row g-1">
                            <?php foreach ($q['options'] as $opt): ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 small p-1 rounded <?= $opt['is_correct'] ? 'bg-success bg-opacity-10' : '' ?>">
                                    <i class="bi bi-<?= $opt['is_correct'] ? 'check-circle-fill text-success' : 'circle text-muted' ?>" style="font-size:0.75rem;flex-shrink:0"></i>
                                    <?= h($opt['text']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
