<?php
$pageTitle = 'Manage Courses';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Delete course
if ($action === 'delete' && $courseId) {
    verify_csrf();
    $stmt = db()->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    set_flash('success', 'Course deleted.');
    header('Location: /admin/courses.php');
    exit;
}

// Handle save (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    verify_csrf();
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $status = in_array($_POST['status'], ['draft', 'published']) ? $_POST['status'] : 'draft';
    $order  = (int)($_POST['order_index'] ?? 0);
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (strlen($title) < 2) {
        set_flash('error', 'Course title is required.');
    } elseif ($id) {
        $stmt = db()->prepare('UPDATE courses SET title=?, description=?, status=?, order_index=? WHERE id=?');
        $stmt->execute([$title, $desc, $status, $order, $id]);
        set_flash('success', 'Course updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO courses (title, description, status, order_index) VALUES (?,?,?,?)');
        $stmt->execute([$title, $desc, $status, $order]);
        set_flash('success', 'Course created.');
    }
    header('Location: /admin/courses.php');
    exit;
}

// Handle save module (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_module'])) {
    verify_csrf();
    $title    = trim($_POST['title'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $courseId2 = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $order    = (int)($_POST['order_index'] ?? 0);
    $mid      = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT);

    // Validate video URL if provided
    if ($videoUrl && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        $videoUrl = ''; // silently clear invalid URL
    }

    if (strlen($title) >= 2 && $courseId2) {
        if ($mid) {
            $stmt = db()->prepare('UPDATE modules SET title=?, description=?, video_url=?, order_index=? WHERE id=?');
            $stmt->execute([$title, $desc, $videoUrl ?: null, $order, $mid]);
        } else {
            $stmt = db()->prepare('INSERT INTO modules (course_id, title, description, video_url, order_index) VALUES (?,?,?,?,?)');
            $stmt->execute([$courseId2, $title, $desc, $videoUrl ?: null, $order]);
        }
        set_flash('success', 'Module saved.');
    }
    header('Location: /admin/courses.php?action=edit&id=' . $courseId2);
    exit;
}

// Load for edit
$editCourse = null;
$modules    = [];
if ($action === 'edit' && $courseId) {
    $editCourse = get_course($courseId);
    $modules    = get_modules_for_course($courseId);
}

$courses = db()->query('SELECT c.*, (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count FROM courses c ORDER BY order_index')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <?php if ($action === 'edit' && $editCourse): ?>
        <!-- Edit course view -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="/admin/courses.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>All Courses</a>
                <h1 class="admin-page-title mb-0 mt-1"><?= h($editCourse['title']) ?></h1>
            </div>
        </div>

        <?php render_flash(); ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <!-- Edit course form -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">Course Details</h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_course" value="1">
                            <input type="hidden" name="id" value="<?= $editCourse['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= h($editCourse['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= h($editCourse['description']) ?></textarea>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?= $editCourse['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="published" <?= $editCourse['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Order</label>
                                    <input type="number" name="order_index" class="form-control" value="<?= $editCourse['order_index'] ?>">
                                </div>
                            </div>
                            <button class="btn btn-primary">Save Course</button>
                        </form>
                    </div>
                </div>

                <!-- Add module form -->
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="fw-700 mb-3">Add Module</h5>
                        <form method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="save_module" value="1">
                            <input type="hidden" name="course_id" value="<?= $editCourse['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Module Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (optional)</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Module Video URL <span class="text-muted small">(optional — YouTube embed or direct URL)</span>
                                </label>
                                <input type="url" name="video_url" class="form-control"
                                       placeholder="https://www.youtube.com/embed/VIDEO_ID">
                                <div class="form-text">Use the YouTube embed URL format: youtube.com/embed/VIDEO_ID</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" name="order_index" class="form-control" value="<?= count($modules) + 1 ?>">
                            </div>
                            <button class="btn btn-outline-primary">Add Module</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <!-- Modules list -->
                <h5 class="fw-700 mb-3">Modules (<?= count($modules) ?>)</h5>
                <?php if (empty($modules)): ?>
                <div class="empty-state card"><div class="card-body"><i class="bi bi-layers text-muted"></i><p>No modules yet. Add one to get started.</p></div></div>
                <?php endif; ?>
                <?php foreach ($modules as $mod):
                    $modLessons = get_lessons_for_module($mod['id']);
                    $quiz = get_quiz_for_module($mod['id']);
                    $isEditingMod = isset($_GET['edit_module']) && (int)$_GET['edit_module'] === $mod['id'];
                ?>
                <div class="card mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1">
                                <h6 class="fw-700 mb-1"><?= h($mod['title']) ?></h6>
                                <p class="text-muted small mb-2"><?= h($mod['description']) ?></p>
                                <div class="d-flex gap-3 small text-muted flex-wrap">
                                    <span><i class="bi bi-file-text me-1"></i><?= count($modLessons) ?> lessons</span>
                                    <span><i class="bi bi-<?= $quiz ? 'check-circle text-success' : 'x-circle text-muted' ?> me-1"></i>Quiz <?= $quiz ? 'set' : 'missing' ?></span>
                                    <?php if ($mod['video_url']): ?>
                                    <span class="text-success"><i class="bi bi-play-btn me-1"></i>Video set</span>
                                    <?php else: ?>
                                    <span class="text-muted"><i class="bi bi-play-btn me-1"></i>No video</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isEditingMod): ?>
                                <!-- Inline edit form for this module -->
                                <form method="POST" class="mt-3 border-top pt-3">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="save_module" value="1">
                                    <input type="hidden" name="course_id" value="<?= $editCourse['id'] ?>">
                                    <input type="hidden" name="module_id" value="<?= $mod['id'] ?>">
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <input type="text" name="title" class="form-control form-control-sm"
                                                   value="<?= h($mod['title']) ?>" placeholder="Title" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" name="order_index" class="form-control form-control-sm"
                                                   value="<?= $mod['order_index'] ?>" placeholder="Order">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" name="description" class="form-control form-control-sm"
                                               value="<?= h($mod['description'] ?? '') ?>" placeholder="Description (optional)">
                                    </div>
                                    <div class="mb-2">
                                        <input type="url" name="video_url" class="form-control form-control-sm"
                                               value="<?= h($mod['video_url'] ?? '') ?>"
                                               placeholder="Video URL (optional — e.g. youtube.com/embed/VIDEO_ID)">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary">Save</button>
                                        <a href="/admin/courses.php?action=edit&id=<?= $editCourse['id'] ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="/admin/courses.php?action=edit&id=<?= $editCourse['id'] ?>&edit_module=<?= $mod['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Edit module">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="/admin/lessons.php?module_id=<?= $mod['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-text"></i> Lessons
                                </a>
                                <a href="/admin/quizzes.php?module_id=<?= $mod['id'] ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-question-circle"></i> Quiz
                                </a>
                                <a href="/admin/courses.php?action=delete_module&module_id=<?= $mod['id'] ?>&course_id=<?= $editCourse['id'] ?>&csrf_token=<?= csrf_token() ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   data-confirm="Delete module '<?= h($mod['title']) ?>' and all its content?">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php elseif ($action === 'new'): ?>
        <!-- New course form -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="/admin/courses.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>All Courses</a>
                <h1 class="admin-page-title mb-0 mt-1">New Course</h1>
            </div>
        </div>
        <?php render_flash(); ?>
        <div class="card" style="max-width:600px">
            <div class="card-body p-4">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_course" value="1">
                    <div class="mb-3">
                        <label class="form-label">Course Title</label>
                        <input type="text" name="title" class="form-control" id="course-title" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Order</label>
                            <input type="number" name="order_index" class="form-control" value="<?= count($courses) + 1 ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Create Course</button>
                        <a href="/admin/courses.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Courses list -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Courses</h1>
            <a href="/admin/courses.php?action=new" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Course
            </a>
        </div>

        <?php render_flash(); ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Modules</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td class="fw-600"><?= h($c['title']) ?></td>
                            <td><?= $c['module_count'] ?></td>
                            <td><span class="badge-chip <?= $c['status'] === 'published' ? 'chip-published' : 'chip-draft' ?>"><?= $c['status'] ?></span></td>
                            <td><?= $c['order_index'] ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="/admin/courses.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="/admin/courses.php?action=delete&id=<?= $c['id'] ?>&csrf_token=<?= csrf_token() ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       data-confirm="Delete course '<?= h($c['title']) ?>' and all its content? This cannot be undone.">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courses)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No courses yet. <a href="/admin/courses.php?action=new">Create one.</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Handle delete module (GET with CSRF in URL — simple approach)
if (isset($_GET['action']) && $_GET['action'] === 'delete_module') {
    $token = $_GET['csrf_token'] ?? '';
    start_session();
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) { die('Invalid token.'); }
    $mid = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);
    $cid = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
    if ($mid) {
        db()->prepare('DELETE FROM modules WHERE id = ?')->execute([$mid]);
        set_flash('success', 'Module deleted.');
        header('Location: /admin/courses.php?action=edit&id=' . $cid);
        exit;
    }
}
require_once __DIR__ . '/../includes/footer.php';
?>
