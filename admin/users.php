<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Toggle active status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    verify_csrf();
    $uid = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($uid && $uid !== current_user()['id']) {
        $stmt = db()->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
        $stmt->execute([$uid]);
    }
    set_flash('success', 'User status updated.');
    header('Location: /admin/users.php');
    exit;
}

// Change role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    verify_csrf();
    $uid  = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $role = in_array($_POST['role'], ['student', 'admin']) ? $_POST['role'] : 'student';
    if ($uid && $uid !== current_user()['id']) {
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
    }
    set_flash('success', 'Role updated.');
    header('Location: /admin/users.php');
    exit;
}

// Search/filter
$search = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';
$params = [];
$where  = [];

if ($search) {
    $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role && in_array($role, ['student', 'admin'])) {
    $where[] = 'u.role = ?';
    $params[] = $role;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("
    SELECT u.*,
        (SELECT COUNT(*) FROM user_enrollments WHERE user_id = u.id) AS enrollments,
        (SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) AS lessons_done
    FROM users u
    $whereSql
    ORDER BY u.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Users</h1>
            <span class="text-muted small"><?= count($users) ?> results</span>
        </div>

        <?php render_flash(); ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body p-3">
                <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
                    <div>
                        <label class="form-label mb-1 small">Search</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Name or email" value="<?= h($search) ?>">
                    </div>
                    <div>
                        <label class="form-label mb-1 small">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All roles</option>
                            <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Students</option>
                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admins</option>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm">Filter</button>
                    <a href="/admin/users.php" class="btn btn-outline-secondary btn-sm">Clear</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email / Phone</th>
                            <th>Location</th>
                            <th>Experience</th>
                            <th>Role</th>
                            <th>Lessons</th>
                            <th>Eligible</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
                            $eligible = is_eligible($u['id']);
                            $expLabels = ['none'=>'Beginner','lt1'=>'< 1 yr','1-2'=>'1–2 yrs','3-5'=>'3–5 yrs','5+'=>'5+ yrs'];
                            $expLabel = $u['years_experience'] ? ($expLabels[$u['years_experience']] ?? $u['years_experience']) : '—';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width:28px;height:28px;font-size:0.75rem;flex-shrink:0">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-500 small"><?= h($u['name']) ?></div>
                                        <?php if ($u['github_url'] || $u['linkedin_url']): ?>
                                        <div class="d-flex gap-1 mt-1">
                                            <?php if ($u['github_url']): ?>
                                            <a href="<?= h($u['github_url']) ?>" target="_blank" class="text-muted" style="font-size:0.75rem"><i class="bi bi-github"></i></a>
                                            <?php endif; ?>
                                            <?php if ($u['linkedin_url']): ?>
                                            <a href="<?= h($u['linkedin_url']) ?>" target="_blank" class="text-muted" style="font-size:0.75rem"><i class="bi bi-linkedin"></i></a>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                <div class="text-muted"><?= h($u['email']) ?></div>
                                <?php if ($u['phone']): ?>
                                <div class="text-muted"><?= h($u['phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= $u['city'] ? h($u['city']) . '<br>' : '' ?>
                                <?= $u['country'] ? h($u['country']) : '—' ?>
                            </td>
                            <td class="small text-muted"><?= h($expLabel) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                    <?= $u['role'] ?>
                                </span>
                            </td>
                            <td class="small"><?= $u['lessons_done'] ?></td>
                            <td>
                                <?php if ($eligible): ?>
                                <span class="text-success fw-600 small"><i class="bi bi-check-circle-fill me-1"></i>Yes</span>
                                <?php else: ?>
                                <span class="text-muted small">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <!-- Detail expand -->
                                <button class="btn btn-sm btn-outline-info mb-1"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#detail-<?= $u['id'] ?>">
                                    <i class="bi bi-person-lines-fill"></i>
                                </button>
                                <?php if ($u['id'] !== current_user()['id']): ?>
                                <div class="d-flex gap-1 flex-wrap">
                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="toggle_active" value="1">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>"
                                                data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?">
                                            <?= $u['is_active'] ? '<i class="bi bi-pause"></i>' : '<i class="bi bi-play"></i>' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="change_role" value="1">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'student' : 'admin' ?>">
                                        <button class="btn btn-sm btn-outline-secondary"
                                                title="<?= $u['role'] === 'admin' ? 'Demote to student' : 'Promote to admin' ?>"
                                                data-confirm="Change this user's role to <?= $u['role'] === 'admin' ? 'student' : 'admin' ?>?">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <span class="text-muted small">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Expandable detail row -->
                        <tr class="collapse" id="detail-<?= $u['id'] ?>">
                            <td colspan="10" class="bg-light p-3">
                                <div class="row g-3 small">
                                    <div class="col-md-4">
                                        <strong>Education:</strong> <?= h($u['education_level'] ?? '—') ?><br>
                                        <strong>Experience:</strong> <?= h($u['years_experience'] ?? '—') ?><br>
                                        <strong>Source:</strong> <?= h($u['how_heard'] ?? '—') ?>
                                    </div>
                                    <div class="col-md-8">
                                        <strong>Bio:</strong>
                                        <p class="mb-0 text-muted"><?= $u['bio'] ? nl2br(h($u['bio'])) : '<em>No bio provided</em>' ?></p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
