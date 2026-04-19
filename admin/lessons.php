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

// No module selected — show overview of all lessons grouped by course/module
if (!$moduleId) {
    $allCourses = db()->query('SELECT * FROM courses ORDER BY order_index')->fetchAll();
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="admin-layout">
        <?php require __DIR__ . '/partials/sidebar.php'; ?>
        <div class="admin-content">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="admin-page-title mb-0">Lessons</h1>
                <a href="/admin/courses.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-collection me-1"></i>Manage Courses
                </a>
            </div>
            <?php render_flash(); ?>
            <?php if (empty($allCourses)): ?>
            <div class="card"><div class="card-body text-center text-muted py-5">No courses yet. <a href="/admin/courses.php?action=new">Create a course</a> first.</div></div>
            <?php endif; ?>
            <?php foreach ($allCourses as $course):
                $modules = get_modules_for_course($course['id']);
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
                    <h5 class="fw-700 mb-0"><?= h($course['title']) ?></h5>
                    <span class="badge-chip <?= $course['status'] === 'published' ? 'chip-published' : 'chip-draft' ?>"><?= $course['status'] ?></span>
                </div>
                <?php if (empty($modules)): ?>
                <div class="card-body text-muted small py-3 px-4">No modules in this course.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:30%">Module</th>
                                <th>Lessons</th>
                                <th style="width:130px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($modules as $mod):
                            $modLessons = get_lessons_for_module($mod['id']);
                        ?>
                            <tr>
                                <td class="fw-600"><?= h($mod['title']) ?></td>
                                <td>
                                    <?php if (empty($modLessons)): ?>
                                    <span class="text-muted small">No lessons yet</span>
                                    <?php else: ?>
                                    <ul class="mb-0 ps-3 small">
                                        <?php foreach ($modLessons as $ls): ?>
                                        <li><?= h($ls['title']) ?><?= $ls['video_url'] ? ' <i class="bi bi-play-btn text-success" title="Has video"></i>' : '' ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="/admin/lessons.php?module_id=<?= $mod['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Manage
                                        </a>
                                        <a href="/admin/lessons.php?module_id=<?= $mod['id'] ?>&action=new" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

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
        <form method="POST" id="lesson-form">
            <?= csrf_field() ?>
            <input type="hidden" name="save_lesson" value="1">
            <input type="hidden" name="lesson_id" value="<?= $editLesson ? $editLesson['id'] : '' ?>">

            <!-- Basic info card -->
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-600">Lesson Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?= h($editLesson['title'] ?? '') ?>" required autofocus placeholder="e.g. Introduction to CSS Flexbox">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Order</label>
                            <input type="number" name="order_index" class="form-control form-control-lg" value="<?= $editLesson ? $editLesson['order_index'] : (count($lessons) + 1) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content editor card -->
            <div class="card mb-3">
                <div class="card-header px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-richtext text-primary"></i>
                        <span class="fw-600">Lesson Content</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <textarea id="lesson-wysiwyg" name="content"><?= htmlspecialchars($editLesson['content'] ?? '', ENT_QUOTES) ?></textarea>
                </div>
            </div>

            <!-- Video section — visually distinct -->
            <div class="lesson-video-card mb-4">
                <div class="lesson-video-card-header">
                    <div class="lesson-video-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div>
                        <div class="fw-600">Video Lesson</div>
                        <div class="small" style="color:var(--text-muted)">Paste a YouTube or Vimeo embed URL — optional</div>
                    </div>
                    <?php if (!empty($editLesson['video_url'])): ?>
                    <span class="badge bg-success ms-auto"><i class="bi bi-check-circle me-1"></i>Video set</span>
                    <?php endif; ?>
                </div>
                <div class="lesson-video-card-body">
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <input type="url" name="video_url" id="video-url-input" class="form-control"
                               value="<?= h($editLesson['video_url'] ?? '') ?>"
                               placeholder="https://www.youtube.com/embed/VIDEO_ID">
                        <button type="button" id="preview-video-btn" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Preview
                        </button>
                        <button type="button" id="clear-video-btn" class="btn btn-outline-danger" <?= empty($editLesson['video_url']) ? 'style="display:none"' : '' ?>>
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="small" style="color:var(--text-muted)">
                        <i class="bi bi-info-circle me-1"></i>
                        Use the embed URL, not the watch URL. YouTube: <code>youtube.com/embed/ID</code> &nbsp;|&nbsp; Vimeo: <code>player.vimeo.com/video/ID</code>
                    </div>
                    <div id="video-preview-container" class="mt-3 <?= empty($editLesson['video_url']) ? 'd-none' : '' ?>">
                        <?php if (!empty($editLesson['video_url'])): ?>
                        <div class="ratio ratio-16x9 lesson-video-preview-frame">
                            <iframe src="<?= h($editLesson['video_url']) ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i><?= $editLesson ? 'Update Lesson' : 'Create Lesson' ?>
                </button>
                <a href="/admin/lessons.php?module_id=<?= $moduleId ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>

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

<?php if ($action === 'new' || $action === 'edit'): ?>
<link href="https://cdn.jsdelivr.net/npm/jodit@3.24.5/build/jodit.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jodit@3.24.5/build/jodit.min.js"></script>
<script>
(function () {
    var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    var isDark = currentTheme === 'dark';

    var editor = Jodit.make('#lesson-wysiwyg', {
        height: 540,
        theme: isDark ? 'dark' : 'default',
        toolbarButtonSize: 'middle',
        buttons: [
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'ul', 'ol', '|',
            'paragraph', '|',
            'link', 'image', '|',
            'table', '|',
            'source', '|',
            'undo', 'redo'
        ],
        extraButtons: [],
        showXPathInStatusbar: false,
        showCharsCounter: true,
        showWordsCounter: true,
        allowResizeX: false,
        allowResizeY: true,
        minHeight: 300,
        defaultMode: Jodit.constants.MODE_WYSIWYG,
        language: 'en',
        style: isDark ? {
            background: '#151B23',
            color: '#ffffff',
        } : {},
        editorClassName: 'lesson-wysiwyg-body',
    });

    // Observe theme changes and update editor theme
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            if (m.attributeName === 'data-bs-theme') {
                var t = document.documentElement.getAttribute('data-bs-theme');
                editor.setTheme(t === 'dark' ? 'dark' : 'default');
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    // Video preview
    var videoInput   = document.getElementById('video-url-input');
    var previewBtn   = document.getElementById('preview-video-btn');
    var clearBtn     = document.getElementById('clear-video-btn');
    var previewCont  = document.getElementById('video-preview-container');

    function renderPreview(url) {
        if (!url) { previewCont.classList.add('d-none'); previewCont.innerHTML = ''; return; }
        previewCont.innerHTML = '<div class="ratio ratio-16x9 lesson-video-preview-frame"><iframe src="' +
            url.replace(/"/g, '&quot;') +
            '" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe></div>';
        previewCont.classList.remove('d-none');
    }

    if (previewBtn) {
        previewBtn.addEventListener('click', function () {
            renderPreview(videoInput.value.trim());
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            videoInput.value = '';
            previewCont.classList.add('d-none');
            previewCont.innerHTML = '';
            clearBtn.style.display = 'none';
        });
    }
    if (videoInput) {
        videoInput.addEventListener('input', function () {
            clearBtn.style.display = this.value.trim() ? '' : 'none';
        });
    }
})();
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
