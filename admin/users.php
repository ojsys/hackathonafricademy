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
$search      = trim($_GET['q'] ?? '');
$role        = $_GET['role'] ?? '';
$statusFilt  = $_GET['status'] ?? '';
$eligible    = $_GET['eligible'] ?? '';
$experience  = $_GET['experience'] ?? '';
$country     = trim($_GET['country'] ?? '');
$minLessons  = $_GET['min_lessons'] ?? '';
$sort        = $_GET['sort'] ?? 'newest';

$params = [];
$where  = [];

if ($search) {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role && in_array($role, ['student', 'admin'])) {
    $where[]  = 'u.role = ?';
    $params[] = $role;
}
if ($statusFilt === 'active') {
    $where[] = 'u.is_active = 1';
} elseif ($statusFilt === 'inactive') {
    $where[] = 'u.is_active = 0';
}
if ($eligible === 'yes') {
    $where[] = 'cr.eligibility_status = "eligible"';
} elseif ($eligible === 'no') {
    $where[] = '(cr.eligibility_status IS NULL OR cr.eligibility_status != "eligible")';
}
if ($experience && in_array($experience, ['none','lt1','1-2','3-5','5+'])) {
    $where[]  = 'u.years_experience = ?';
    $params[] = $experience;
}
if ($country) {
    $where[]  = 'u.country LIKE ?';
    $params[] = "%$country%";
}
if ($minLessons !== '' && is_numeric($minLessons)) {
    $where[]  = '(SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) >= ?';
    $params[] = (int) $minLessons;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sortMap = [
    'newest'       => 'u.created_at DESC',
    'oldest'       => 'u.created_at ASC',
    'lessons_desc' => 'lessons_done DESC',
    'score_desc'   => 'COALESCE(cr.total_score, 0) DESC',
    'quiz_desc'    => 'COALESCE(cr.avg_quiz_score, 0) DESC',
    'name_asc'     => 'u.name ASC',
];
$orderSql = $sortMap[$sort] ?? 'u.created_at DESC';

$perPage = 25;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$countStmt = db()->prepare("SELECT COUNT(*) FROM users u LEFT JOIN candidate_reviews cr ON cr.user_id = u.id $whereSql");
$countStmt->execute($params);
$totalUsers = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalUsers / $perPage);

$stmt = db()->prepare("
    SELECT u.*,
        (SELECT COUNT(*) FROM user_enrollments WHERE user_id = u.id) AS enrollments,
        (SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) AS lessons_done,
        cr.total_score,
        cr.avg_quiz_score,
        cr.courses_completed
    FROM users u
    LEFT JOIN candidate_reviews cr ON cr.user_id = u.id
    $whereSql
    ORDER BY $orderSql
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();

$baseQueryParams = array_filter([
    'q'           => $search,
    'role'        => $role,
    'status'      => $statusFilt,
    'eligible'    => $eligible,
    'experience'  => $experience,
    'country'     => $country,
    'min_lessons' => $minLessons,
    'sort'        => $sort !== 'newest' ? $sort : '',
]);
$paginationBase = '/admin/users.php?' . ($baseQueryParams ? http_build_query($baseQueryParams) . '&' : '');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="admin-page-title mb-0">Users</h1>
            <span class="text-muted small"><?= $totalUsers ?> total &mdash; page <?= $page ?> of <?= max(1, $totalPages) ?></span>
        </div>

        <?php render_flash(); ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body p-3">
                <form method="GET">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label mb-1 small">Search</label>
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Name or email" value="<?= h($search) ?>">
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All roles</option>
                                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Students</option>
                                <option value="admin"   <?= $role === 'admin'   ? 'selected' : '' ?>>Admins</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Account Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active"   <?= $statusFilt === 'active'   ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $statusFilt === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Eligible</label>
                            <select name="eligible" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="yes" <?= $eligible === 'yes' ? 'selected' : '' ?>>Eligible only</option>
                                <option value="no"  <?= $eligible === 'no'  ? 'selected' : '' ?>>Not eligible</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <label class="form-label mb-1 small">Sort by</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="newest"       <?= $sort === 'newest'       ? 'selected' : '' ?>>Newest joined</option>
                                <option value="oldest"       <?= $sort === 'oldest'       ? 'selected' : '' ?>>Oldest joined</option>
                                <option value="lessons_desc" <?= $sort === 'lessons_desc' ? 'selected' : '' ?>>Top learners (lessons)</option>
                                <option value="score_desc"   <?= $sort === 'score_desc'   ? 'selected' : '' ?>>Top score</option>
                                <option value="quiz_desc"    <?= $sort === 'quiz_desc'    ? 'selected' : '' ?>>Top quiz score</option>
                                <option value="name_asc"     <?= $sort === 'name_asc'     ? 'selected' : '' ?>>Name A–Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Experience</label>
                            <select name="experience" class="form-select form-select-sm">
                                <option value="">Any</option>
                                <option value="none" <?= $experience === 'none' ? 'selected' : '' ?>>Beginner (none)</option>
                                <option value="lt1"  <?= $experience === 'lt1'  ? 'selected' : '' ?>>< 1 year</option>
                                <option value="1-2"  <?= $experience === '1-2'  ? 'selected' : '' ?>>1–2 years</option>
                                <option value="3-5"  <?= $experience === '3-5'  ? 'selected' : '' ?>>3–5 years</option>
                                <option value="5+"   <?= $experience === '5+'   ? 'selected' : '' ?>>5+ years</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Country</label>
                            <input type="text" name="country" class="form-control form-control-sm" placeholder="e.g. Nigeria" value="<?= h($country) ?>">
                        </div>
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label mb-1 small">Min Lessons</label>
                            <input type="number" name="min_lessons" class="form-control form-control-sm" placeholder="e.g. 5" min="0" value="<?= h($minLessons) ?>">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">Apply</button>
                            <a href="/admin/users.php" class="btn btn-outline-secondary btn-sm ms-1">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email / Phone</th>
                            <th>Location</th>
                            <th>Experience</th>
                            <th>Role</th>
                            <th>Lessons</th>
                            <th>Score</th>
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
                            <td class="small">
                                <?php if ($u['total_score'] !== null): ?>
                                <span class="fw-600"><?= round($u['total_score']) ?>%</span>
                                <?php if ($u['avg_quiz_score'] !== null): ?>
                                <div class="text-muted" style="font-size:.7rem">quiz <?= round($u['avg_quiz_score']) ?>%</div>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
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
                                <a href="/admin/edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary mb-1" title="Edit user">
                                    <i class="bi bi-pencil"></i>
                                </a>
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
                            <td colspan="11" class="bg-light p-3">
                                <div class="row g-3 small">
                                    <div class="col-md-4">
                                        <strong>Education:</strong> <?= h($u['education_level'] ?? '—') ?><br>
                                        <strong>Experience:</strong> <?= h($u['years_experience'] ?? '—') ?><br>
                                        <strong>Gender:</strong> <?= $u['gender'] ? ucfirst(h($u['gender'])) : '—' ?><br>
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
                        <tr><td colspan="11" class="text-center text-muted py-4">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer border-top py-2" style="border-color:var(--border)!important">
                <?= render_pagination($page, $totalPages, $paginationBase) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
