<?php
$pageTitle = 'Manage Lessons';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$moduleId = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);
$action   = $_GET['action'] ?? 'list';
$lessonId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$moduleId && $lessonId) {
    $l = get_lesson($lessonId);
    if ($l) $moduleId = $l['module_id'];
}

if (!$moduleId) { header('Location: /admin/courses.php'); exit; }

$stmt = db()->prepare('SELECT m.*, c.id AS course_id, c.title AS course_title FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?');
$stmt->execute([$moduleId]);
$module = $stmt->fetch();
if (!$module) { header('Location: /admin/courses.php'); exit; }

// Handle delete
if ($action === 'delete' && $lessonId) {
    verify_csrf();
    db()->prepare('DELETE FROM lessons WHERE id = ?')->execute([$lessonId]);
    set_flash('success', 'Lesson deleted.');
    header('Location: /admin/lessons.php?module_id=' . $moduleId);
    exit;
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lesson'])) {
    verify_csrf();
    $title    = trim($_POST['title'] ?? '');
    $content  = $_POST['content'] ?? '';  // WYSIWYG HTML — sanitize in production
    $videoUrl = trim($_POST['video_url'] ?? '');
    $order    = (int)($_POST['order_index'] ?? 0);
    $lid      = filter_input(INPUT_POST, 'lesson_id', FILTER_VALIDATE_INT);

    if (strlen($title) < 2) {
        set_flash('error', 'Lesson title is required.');
        header('Location: /admin/lessons.php?module_id=' . $moduleId . ($lid ? '&action=edit&id=' . $lid : '&action=new'));
        exit;
    }

    if ($lid) {
        $stmt = db()->prepare('UPDATE lessons SET title=?, content=?, video_url=?, order_index=? WHERE id=?');
        $stmt->execute([$title, $content, $videoUrl ?: null, $order, $lid]);
        set_flash('success', 'Lesson updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO lessons (module_id, title, content, video_url, order_index) VALUES (?,?,?,?,?)');
        $stmt->execute([$moduleId, $title, $content, $videoUrl ?: null, $order]);
        set_flash('success', 'Lesson created.');
    }
    header('Location: /admin/lessons.php?module_id=' . $moduleId);
    exit;
}

$lessons   = get_lessons_for_module($moduleId);
$editLesson = ($action === 'edit' && $lessonId) ? get_lesson($lessonId) : null;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/courses.php">Courses</a></li>
                <li class="breadcrumb-item"><a href="/admin/courses.php?action=edit&id=<?= $module['course_id'] ?>"><?= h($module['course_title']) ?></a></li>
                <li class="breadcrumb-item active"><?= h($module['title']) ?> — Lessons</li>
            </ol>
        </nav>

        <?php render_flash(); ?>

        <?php if ($action === 'new' || $action === 'edit'): ?>
        <!-- Lesson form -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0"><?= $editLesson ? 'Edit Lesson' : 'New Lesson' ?></h1>
            <a href="/admin/lessons.php?module_id=<?= $moduleId ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Lessons
            </a>
        </div>
        <div class="card">
            <div class="card-body p-4">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_lesson" value="1">
                    <input type="hidden" name="lesson_id" value="<?= $editLesson ? $editLesson['id'] : '' ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Lesson Title</label>
                            <input type="text" name="title" class="form-control" value="<?= h($editLesson['title'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input type="number" name="order_index" class="form-control" value="<?= $editLesson ? $editLesson['order_index'] : (count($lessons) + 1) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Video URL (optional)</label>
                        <input type="url" name="video_url" class="form-control" value="<?= h($editLesson['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/embed/...">
                    </div>
                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center justify-content-between">
                            <span>Content <span class="text-muted fw-400">(HTML supported)</span></span>
                        </label>
                        <textarea name="content" class="form-control" rows="20" style="font-family:monospace;font-size:0.85rem;"><?= htmlspecialchars($editLesson['content'] ?? '', ENT_QUOTES) ?></textarea>
                        <small class="text-muted">Write HTML directly. Wrap code examples in <code>&lt;div class="code-block"&gt;&lt;pre&gt;&lt;code&gt;...&lt;/code&gt;&lt;/pre&gt;&lt;/div&gt;</code></small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><?= $editLesson ? 'Update Lesson' : 'Create Lesson' ?></button>
                        <a href="/admin/lessons.php?module_id=<?= $moduleId ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Lessons list -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Lessons: <?= h($module['title']) ?></h1>
            <a href="/admin/lessons.php?module_id=<?= $moduleId ?>&action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Lesson
            </a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Video</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lessons as $l): ?>
                        <tr>
                            <td class="text-muted"><?= $l['order_index'] ?></td>
                            <td class="fw-500"><?= h($l['title']) ?></td>
                            <td><?= $l['video_url'] ? '<i class="bi bi-play-btn text-success"></i>' : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="/admin/lessons.php?module_id=<?= $moduleId ?>&action=edit&id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="/pages/lesson.php?id=<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                    <a href="/admin/lessons.php?module_id=<?= $moduleId ?>&action=delete&id=<?= $l['id'] ?>&csrf_token=<?= csrf_token() ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       data-confirm="Delete this lesson?"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lessons)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No lessons yet. <a href="/admin/lessons.php?module_id=<?= $moduleId ?>&action=new">Add one.</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
