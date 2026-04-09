<?php
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/functions.php';
start_session();
if (is_logged_in()) { header('Location: /pages/dashboard.php'); exit; }
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width:680px">
    <div class="text-center mb-4">
        <img src="<?= h($siteSettings['logo_path'] ?? '/public/img/logo.png') ?>" alt="HackathonAfrica" style="height: 48px; width: auto; margin: 0 auto; display: block;" class="mb-3">
        <h3 class="fw-700 mb-1">Join HackathonAfrica</h3>
        <p class="text-muted">Create your learner profile to begin the qualification track</p>
    </div>

    <?php render_flash(); ?>

    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <form action="/actions/register.php" method="POST" novalidate>
                <?= csrf_field() ?>

                <!-- Section: Account Info -->
                <h6 class="fw-700 text-uppercase text-muted small letter-spacing mb-3">Account Information</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="e.g. Amara Osei"
                               value="<?= h($_POST['name'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="you@example.com"
                               value="<?= h($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Min 8 characters" minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirm">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                               placeholder="Repeat your password" required>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Section: Personal Info -->
                <h6 class="fw-700 text-uppercase text-muted small letter-spacing mb-3">Personal Information</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               placeholder="+233 20 000 0000"
                               value="<?= h($_POST['phone'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="country">Country <span class="text-danger">*</span></label>
                        <select class="form-select" id="country" name="country" required>
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
                            $selected = $_POST['country'] ?? '';
                            foreach ($countries as $c) {
                                echo '<option value="' . h($c) . '"' . ($selected === $c ? ' selected' : '') . '>' . h($c) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="city">City / State <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="city" name="city"
                               placeholder="e.g. Accra"
                               value="<?= h($_POST['city'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="how_heard">How did you hear about us? <span class="text-danger">*</span></label>
                        <select class="form-select" id="how_heard" name="how_heard" required>
                            <option value="">Select…</option>
                            <?php
                            $sources = ['Social Media (Twitter/X)','Social Media (Instagram)','Social Media (LinkedIn)',
                                'Social Media (Facebook)','Friend or Colleague','Google Search','Email Newsletter',
                                'Tech Event / Meetup','University / College','YouTube','Other'];
                            $selSrc = $_POST['how_heard'] ?? '';
                            foreach ($sources as $s) {
                                echo '<option value="' . h($s) . '"' . ($selSrc === $s ? ' selected' : '') . '>' . h($s) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Section: Background -->
                <h6 class="fw-700 text-uppercase text-muted small letter-spacing mb-3">Background &amp; Skills</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="education_level">Highest Education Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="education_level" name="education_level" required>
                            <option value="">Select…</option>
                            <?php
                            $edus = [
                                'high_school'   => 'High School / O-Levels',
                                'diploma'        => 'Diploma / Certificate',
                                'bachelors'      => 'Bachelor\'s Degree',
                                'masters'        => 'Master\'s Degree',
                                'phd'            => 'PhD / Doctorate',
                                'self_taught'    => 'Self-Taught',
                                'other'          => 'Other',
                            ];
                            $selEdu = $_POST['education_level'] ?? '';
                            foreach ($edus as $val => $label) {
                                echo '<option value="' . h($val) . '"' . ($selEdu === $val ? ' selected' : '') . '>' . h($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="years_experience">Coding Experience <span class="text-danger">*</span></label>
                        <select class="form-select" id="years_experience" name="years_experience" required>
                            <option value="">Select…</option>
                            <?php
                            $exps = [
                                'none'      => 'None — complete beginner',
                                'lt1'       => 'Less than 1 year',
                                '1-2'       => '1–2 years',
                                '3-5'       => '3–5 years',
                                '5+'        => 'More than 5 years',
                            ];
                            $selExp = $_POST['years_experience'] ?? '';
                            foreach ($exps as $val => $label) {
                                echo '<option value="' . h($val) . '"' . ($selExp === $val ? ' selected' : '') . '>' . h($label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="github_url">GitHub Profile URL <span class="text-muted small">(optional)</span></label>
                        <input type="url" class="form-control" id="github_url" name="github_url"
                               placeholder="https://github.com/yourusername"
                               value="<?= h($_POST['github_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="linkedin_url">LinkedIn Profile URL <span class="text-muted small">(optional)</span></label>
                        <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                               placeholder="https://linkedin.com/in/yourname"
                               value="<?= h($_POST['linkedin_url'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="skill_level">Self-Assessed Skill Level <span class="text-danger">*</span></label>
                    <select class="form-select" id="skill_level" name="skill_level" required>
                        <option value="">Select…</option>
                        <option value="beginner"     <?= ($_POST['skill_level'] ?? '') === 'beginner'     ? 'selected' : '' ?>>Beginner — little to no coding experience</option>
                        <option value="intermediate" <?= ($_POST['skill_level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate — some HTML/CSS knowledge</option>
                        <option value="advanced"     <?= ($_POST['skill_level'] ?? '') === 'advanced'     ? 'selected' : '' ?>>Advanced — comfortable with web development</option>
                    </select>
                    <div class="form-text">This is for analytics only and does not affect your selection.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="bio">Why do you want to join HackathonAfrica? <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="bio" name="bio" rows="3"
                              placeholder="Tell us briefly about yourself and why you want to join HackathonAfrica…"
                              maxlength="500" required><?= h($_POST['bio'] ?? '') ?></textarea>
                    <div class="form-text">Max 500 characters.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-600">
                    <i class="bi bi-person-plus me-2"></i>Create My Learner Account
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                Already have an account? <a href="/pages/login.php">Sign in</a>
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
