<?php
$pageTitle = 'Edit User';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId) { header('Location: /admin/users.php'); exit; }

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$u = $stmt->fetch();
if (!$u) { set_flash('error', 'User not found.'); header('Location: /admin/users.php'); exit; }

// ── Handle save ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    verify_csrf();

    $name           = trim($_POST['name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $country        = trim($_POST['country'] ?? '');
    $city           = trim($_POST['city'] ?? '');
    $educationLevel = trim($_POST['education_level'] ?? '');
    $yearsExp       = trim($_POST['years_experience'] ?? '');
    $gender         = in_array($_POST['gender'] ?? '', ['male','female','other']) ? $_POST['gender'] : null;
    $githubUrl      = trim($_POST['github_url'] ?? '');
    $linkedinUrl    = trim($_POST['linkedin_url'] ?? '');
    $howHeard       = trim($_POST['how_heard'] ?? '');
    $bio            = trim($_POST['bio'] ?? '');
    $role           = in_array($_POST['role'] ?? '', ['student','admin','superadmin']) ? $_POST['role'] : 'student';
    $isActive       = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if (!$name)                         $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($bio) > 500)             $errors[] = 'Bio must not exceed 500 characters.';
    if ($githubUrl && !filter_var($githubUrl, FILTER_VALIDATE_URL))   $errors[] = 'Invalid GitHub URL.';
    if ($linkedinUrl && !filter_var($linkedinUrl, FILTER_VALIDATE_URL)) $errors[] = 'Invalid LinkedIn URL.';

    // Check email uniqueness (excluding this user)
    if (!$errors) {
        $ck = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $ck->execute([$email, $userId]);
        if ($ck->fetch()) $errors[] = 'That email is already in use by another account.';
    }

    if ($errors) {
        // Re-populate $u with submitted values so form stays filled
        $u = array_merge($u, compact(
            'name','email','phone','country','city','educationLevel','yearsExp',
            'gender','githubUrl','linkedinUrl','howHeard','bio','role','isActive'
        ));
        $u['education_level']  = $educationLevel;
        $u['years_experience'] = $yearsExp;
        $u['github_url']       = $githubUrl;
        $u['linkedin_url']     = $linkedinUrl;
        $u['how_heard']        = $howHeard;
        $u['is_active']        = $isActive;
        set_flash('error', implode(' ', $errors));
    } else {
        db()->prepare('
            UPDATE users SET
                name = ?, email = ?, phone = ?, country = ?, city = ?,
                education_level = ?, years_experience = ?, gender = ?,
                github_url = ?, linkedin_url = ?, how_heard = ?, bio = ?,
                role = ?, is_active = ?
            WHERE id = ?
        ')->execute([
            $name, $email, $phone ?: null, $country ?: null, $city ?: null,
            $educationLevel ?: null, $yearsExp ?: null, $gender,
            $githubUrl ?: null, $linkedinUrl ?: null, $howHeard ?: null, $bio ?: null,
            $role, $isActive,
            $userId,
        ]);

        // Optional password reset
        if (!empty($_POST['new_password'])) {
            $pw = $_POST['new_password'];
            if (strlen($pw) < 8) {
                set_flash('error', 'Password must be at least 8 characters.');
            } else {
                db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                     ->execute([password_hash($pw, PASSWORD_BCRYPT), $userId]);
                set_flash('success', 'User updated and password changed.');
            }
        } else {
            set_flash('success', 'User details updated successfully.');
        }

        header('Location: /admin/edit_user.php?id=' . $userId);
        exit;
    }
}

$edus = [
    'high_school'  => 'High School / O-Levels',
    'diploma'      => 'Diploma / Certificate',
    'bachelors'    => "Bachelor's Degree",
    'masters'      => "Master's Degree",
    'phd'          => 'PhD / Doctorate',
    'self_taught'  => 'Self-Taught',
    'other'        => 'Other',
];
$exps = [
    'none' => 'None — complete beginner',
    'lt1'  => 'Less than 1 year',
    '1-2'  => '1–2 years',
    '3-5'  => '3–5 years',
    '5+'   => 'More than 5 years',
];
$countries = ['Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi','Cameroon',
    'Cape Verde','Central African Republic','Chad','Comoros','Congo','Côte d\'Ivoire',
    'Djibouti','DR Congo','Egypt','Equatorial Guinea','Eritrea','Eswatini','Ethiopia',
    'Gabon','Gambia','Ghana','Guinea','Guinea-Bissau','Kenya','Lesotho','Liberia',
    'Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco','Mozambique',
    'Namibia','Niger','Nigeria','Rwanda','São Tomé and Príncipe','Senegal','Seychelles',
    'Sierra Leone','Somalia','South Africa','South Sudan','Sudan','Tanzania','Togo',
    'Tunisia','Uganda','Zambia','Zimbabwe','Other'];
$sources = ['Social Media (Twitter/X)','Social Media (Instagram)','Social Media (LinkedIn)',
    'Social Media (Facebook)','Friend or Colleague','Google Search','Email Newsletter',
    'Tech Event / Meetup','University / College','YouTube','Other'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">

        <!-- Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="/admin/users.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>All Users
            </a>
            <div>
                <h1 class="admin-page-title mb-0">Edit User</h1>
                <p class="text-muted small mb-0">ID #<?= $userId ?> &mdash; joined <?= date('M j, Y', strtotime($u['created_at'])) ?></p>
            </div>
        </div>

        <?php render_flash(); ?>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="save_user" value="1">

            <div class="row g-4">

                <!-- Left column -->
                <div class="col-lg-8">

                    <!-- Personal info -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-4">Personal Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= h($u['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?= h($u['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?= h($u['phone'] ?? '') ?>" placeholder="+234…">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Prefer not to say</option>
                                        <option value="male"   <?= ($u['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($u['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other"  <?= ($u['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-4">Location</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Country</label>
                                    <select name="country" class="form-select">
                                        <option value="">Select country…</option>
                                        <?php foreach ($countries as $c): ?>
                                        <option value="<?= h($c) ?>" <?= ($u['country'] ?? '') === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City / State</label>
                                    <input type="text" name="city" class="form-control" value="<?= h($u['city'] ?? '') ?>" placeholder="e.g. Lagos">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-4">Background &amp; Skills</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Highest Education Level</label>
                                    <select name="education_level" class="form-select">
                                        <option value="">Select…</option>
                                        <?php foreach ($edus as $val => $label): ?>
                                        <option value="<?= h($val) ?>" <?= ($u['education_level'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Coding Experience</label>
                                    <select name="years_experience" class="form-select">
                                        <option value="">Select…</option>
                                        <?php foreach ($exps as $val => $label): ?>
                                        <option value="<?= h($val) ?>" <?= ($u['years_experience'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GitHub Profile URL</label>
                                    <input type="url" name="github_url" class="form-control" value="<?= h($u['github_url'] ?? '') ?>" placeholder="https://github.com/username">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">LinkedIn Profile URL</label>
                                    <input type="url" name="linkedin_url" class="form-control" value="<?= h($u['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/name">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">How did you hear about us?</label>
                                    <select name="how_heard" class="form-select">
                                        <option value="">Select…</option>
                                        <?php foreach ($sources as $s): ?>
                                        <option value="<?= h($s) ?>" <?= ($u['how_heard'] ?? '') === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Bio / Motivation</label>
                                    <textarea name="bio" class="form-control" rows="4" maxlength="500" placeholder="About this user…"><?= h($u['bio'] ?? '') ?></textarea>
                                    <div class="form-text">Max 500 characters.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password reset -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-1">Reset Password</h5>
                            <p class="text-muted small mb-3">Leave blank to keep the current password.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" minlength="8" autocomplete="new-password" placeholder="Min 8 characters">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right column -->
                <div class="col-lg-4">

                    <!-- Account settings -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-4">Account Settings</h5>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" <?= $userId === current_user()['id'] ? 'disabled' : '' ?>>
                                    <option value="student"    <?= $u['role'] === 'student'    ? 'selected' : '' ?>>Student</option>
                                    <option value="admin"      <?= $u['role'] === 'admin'      ? 'selected' : '' ?>>Admin</option>
                                    <option value="superadmin" <?= $u['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                                </select>
                                <?php if ($userId === current_user()['id']): ?>
                                <input type="hidden" name="role" value="<?= h($u['role']) ?>">
                                <div class="form-text">You cannot change your own role.</div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           value="1" <?= $u['is_active'] ? 'checked' : '' ?>
                                           <?= $userId === current_user()['id'] ? 'disabled' : '' ?>>
                                    <?php if ($userId === current_user()['id']): ?>
                                    <input type="hidden" name="is_active" value="1">
                                    <?php endif; ?>
                                    <label class="form-check-label" for="is_active">Account Active</label>
                                </div>
                                <?php if ($userId === current_user()['id']): ?>
                                <div class="form-text">You cannot deactivate your own account.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick stats -->
                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-700 mb-3">Quick Stats</h5>
                            <?php
                            $lessons = db()->prepare('SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = ?');
                            $lessons->execute([$userId]);
                            $enrollments = db()->prepare('SELECT COUNT(*) FROM user_enrollments WHERE user_id = ?');
                            $enrollments->execute([$userId]);
                            $review = db()->prepare('SELECT * FROM candidate_reviews WHERE user_id = ?');
                            $review->execute([$userId]);
                            $rev = $review->fetch();
                            ?>
                            <dl class="row g-0 small mb-0">
                                <dt class="col-7 text-muted">Enrolled courses</dt>
                                <dd class="col-5 fw-600 mb-2"><?= $enrollments->fetchColumn() ?></dd>
                                <dt class="col-7 text-muted">Lessons completed</dt>
                                <dd class="col-5 fw-600 mb-2"><?= $lessons->fetchColumn() ?></dd>
                                <dt class="col-7 text-muted">Total score</dt>
                                <dd class="col-5 fw-600 mb-2"><?= $rev ? round($rev['total_score']) . '%' : '—' ?></dd>
                                <dt class="col-7 text-muted">Quiz avg</dt>
                                <dd class="col-5 fw-600 mb-2"><?= $rev ? round($rev['avg_quiz_score']) . '%' : '—' ?></dd>
                                <dt class="col-7 text-muted">Eligibility</dt>
                                <dd class="col-5 fw-600 mb-0">
                                    <?php if ($rev && $rev['eligibility_status'] === 'eligible'): ?>
                                    <span class="text-success">Eligible</span>
                                    <?php elseif ($rev): ?>
                                    <?= ucfirst(str_replace('_', ' ', $rev['eligibility_status'])) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                        <a href="/admin/users.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>
                        <a href="/admin/candidates.php?q=<?= urlencode($u['email']) ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-person-check me-1"></i>View in Candidate Review
                        </a>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
