<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();
$eligible = is_eligible($user['id']);
$courses = get_all_published_courses();

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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div style="width:72px;height:72px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;flex-shrink:0;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="mb-0"><?= h($user['name']) ?></h1>
                <p class="mb-0 opacity-75">
                    <?= h($user['email']) ?>
                    <?php if ($user['country']): ?>&middot; <?= h($user['country']) ?><?php endif; ?>
                    &middot; Member since <?= date('F Y', strtotime($user['created_at'])) ?>
                </p>
                <?php if ($user['github_url'] || $user['linkedin_url']): ?>
                <div class="mt-1 d-flex gap-3">
                    <?php if ($user['github_url']): ?>
                    <a href="<?= h($user['github_url']) ?>" target="_blank" rel="noopener" class="text-white opacity-75">
                        <i class="bi bi-github me-1"></i>GitHub
                    </a>
                    <?php endif; ?>
                    <?php if ($user['linkedin_url']): ?>
                    <a href="<?= h($user['linkedin_url']) ?>" target="_blank" rel="noopener" class="text-white opacity-75">
                        <i class="bi bi-linkedin me-1"></i>LinkedIn
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($eligible): ?>
            <div class="ms-auto eligibility-badge eligible">
                <i class="bi bi-trophy-fill"></i> HackathonAfrica Eligible
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-4">
    <?php render_flash(); ?>

    <div class="row g-4">
        <div class="col-lg-8">

            <!-- Account Info -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-4">Account Information</h5>
                    <form action="/actions/update_profile.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="account">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?= h($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?= h($user['email']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="<?= h($user['phone'] ?? '') ?>" placeholder="+233 20 000 0000">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Account Info
                        </button>
                    </form>
                </div>
            </div>

            <!-- Personal / Background Info -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-4">Personal &amp; Background Information</h5>
                    <form action="/actions/update_profile.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="background">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select class="form-select" name="country">
                                    <option value="">Select country…</option>
                                    <?php
                                    $countries = ['Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi','Cameroon',
                                        'Cape Verde','Central African Republic','Chad','Comoros','Congo','Côte d\'Ivoire',
                                        'Djibouti','DR Congo','Egypt','Equatorial Guinea','Eritrea','Eswatini','Ethiopia',
                                        'Gabon','Gambia','Ghana','Guinea','Guinea-Bissau','Kenya','Lesotho','Liberia',
                                        'Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco','Mozambique',
                                        'Namibia','Niger','Nigeria','Rwanda','São Tomé and Príncipe','Senegal','Seychelles',
                                        'Sierra Leone','Somalia','South Africa','South Sudan','Sudan','Tanzania','Togo',
                                        'Tunisia','Uganda','Zambia','Zimbabwe','Other'];
                                    foreach ($countries as $c) {
                                        echo '<option value="' . h($c) . '"' . ($user['country'] === $c ? ' selected' : '') . '>' . h($c) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City / State</label>
                                <input type="text" class="form-control" name="city" value="<?= h($user['city'] ?? '') ?>" placeholder="e.g. Accra">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Highest Education Level</label>
                                <select class="form-select" name="education_level">
                                    <option value="">Select…</option>
                                    <?php foreach ($edus as $val => $label): ?>
                                    <option value="<?= h($val) ?>" <?= ($user['education_level'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Coding Experience</label>
                                <select class="form-select" name="years_experience">
                                    <option value="">Select…</option>
                                    <?php foreach ($exps as $val => $label): ?>
                                    <option value="<?= h($val) ?>" <?= ($user['years_experience'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">GitHub Profile URL</label>
                                <input type="url" class="form-control" name="github_url"
                                       value="<?= h($user['github_url'] ?? '') ?>"
                                       placeholder="https://github.com/yourusername">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LinkedIn Profile URL</label>
                                <input type="url" class="form-control" name="linkedin_url"
                                       value="<?= h($user['linkedin_url'] ?? '') ?>"
                                       placeholder="https://linkedin.com/in/yourname">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">How did you hear about us?</label>
                            <select class="form-select" name="how_heard">
                                <option value="">Select…</option>
                                <?php
                                $sources = ['Social Media (Twitter/X)','Social Media (Instagram)','Social Media (LinkedIn)',
                                    'Social Media (Facebook)','Friend or Colleague','Google Search','Email Newsletter',
                                    'Tech Event / Meetup','University / College','YouTube','Other'];
                                foreach ($sources as $s) {
                                    echo '<option value="' . h($s) . '"' . (($user['how_heard'] ?? '') === $s ? ' selected' : '') . '>' . h($s) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio / Motivation</label>
                            <textarea class="form-control" name="bio" rows="4" maxlength="500"
                                      placeholder="Tell us about yourself and your goals…"><?= h($user['bio'] ?? '') ?></textarea>
                            <div class="form-text">Max 500 characters.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Background Info
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change password -->
            <div class="card">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-4">Change Password</h5>
                    <form action="/actions/change_password.php" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" minlength="8" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-lock me-1"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: profile summary + progress -->
        <div class="col-lg-4">

            <!-- Profile completeness -->
            <?php
            $fields = ['phone','country','city','education_level','years_experience','bio','github_url','linkedin_url'];
            $filled = array_filter($fields, fn($f) => !empty($user[$f]));
            $pct = round(count($filled) / count($fields) * 100);
            ?>
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3">Profile Completeness</h6>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span><?= count($filled) ?>/<?= count($fields) ?> fields filled</span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                             style="width:<?= $pct ?>%"></div>
                    </div>
                    <?php if ($pct < 100): ?>
                    <p class="small text-muted mb-0">Complete your profile so we know more about you as a candidate.</p>
                    <?php else: ?>
                    <p class="small text-success mb-0"><i class="bi bi-check-circle-fill me-1"></i>Profile is complete!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student snapshot -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3">Your Information</h6>
                    <dl class="row small mb-0" style="row-gap:0.5rem">
                        <?php if ($user['phone']): ?>
                        <dt class="col-5 text-muted fw-500 mb-0">Phone</dt>
                        <dd class="col-7 mb-0"><?= h($user['phone']) ?></dd>
                        <?php endif; ?>
                        <?php if ($user['country']): ?>
                        <dt class="col-5 text-muted fw-500 mb-0">Location</dt>
                        <dd class="col-7 mb-0"><?= h($user['city'] ? $user['city'] . ', ' . $user['country'] : $user['country']) ?></dd>
                        <?php endif; ?>
                        <?php if ($user['education_level'] && isset($edus[$user['education_level']])): ?>
                        <dt class="col-5 text-muted fw-500 mb-0">Education</dt>
                        <dd class="col-7 mb-0"><?= h($edus[$user['education_level']]) ?></dd>
                        <?php endif; ?>
                        <?php if ($user['years_experience'] && isset($exps[$user['years_experience']])): ?>
                        <dt class="col-5 text-muted fw-500 mb-0">Experience</dt>
                        <dd class="col-7 mb-0"><?= h($exps[$user['years_experience']]) ?></dd>
                        <?php endif; ?>
                        <?php if ($user['how_heard']): ?>
                        <dt class="col-5 text-muted fw-500 mb-0">Source</dt>
                        <dd class="col-7 mb-0"><?= h($user['how_heard']) ?></dd>
                        <?php endif; ?>
                    </dl>
                    <?php if ($user['bio']): ?>
                    <hr class="my-3">
                    <p class="small text-muted mb-0"><?= nl2br(h($user['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Learning progress -->
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3">Learning Progress</h6>
                    <?php foreach ($courses as $c):
                        if (!is_enrolled($user['id'], $c['id'])) continue;
                        $p = get_course_progress($user['id'], $c['id']);
                        $courseDone = is_course_complete($user['id'], $c['id']);
                    ?>
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fw-600 small"><?= h($c['title']) ?></span>
                            <?php if ($courseDone): ?>
                            <span class="badge bg-success rounded-pill">Done</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span><?= $p['done'] ?>/<?= $p['total'] ?> lessons</span>
                            <span><?= $p['percent'] ?>%</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar" data-width="<?= $p['percent'] ?>" style="width:0"></div>
                        </div>

                        <?php $modList = get_modules_for_course($c['id']); ?>
                        <?php foreach ($modList as $m):
                            $mq = get_quiz_for_module($m['id']);
                            if (!$mq) continue;
                            $att = get_best_quiz_attempt($user['id'], $mq['id']);
                        ?>
                        <div class="d-flex align-items-center gap-2 mt-1 small">
                            <i class="bi bi-<?= ($att && $att['passed']) ? 'check-circle-fill text-success' : 'x-circle text-danger' ?>" style="font-size:0.75rem"></i>
                            <span class="text-muted"><?= h($m['title']) ?> Quiz</span>
                            <?php if ($att): ?>
                            <span class="ms-auto <?= $att['passed'] ? 'text-success' : 'text-danger' ?>"><?= $att['score'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($eligible): ?>
                    <div class="alert alert-success small mb-0">
                        <i class="bi bi-trophy-fill me-1"></i>
                        You are eligible for <strong>HackathonAfrica</strong>!
                    </div>
                    <?php else: ?>
                    <p class="text-muted small mb-0">Complete all courses and pass all quizzes to earn eligibility.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
