<?php
$pageTitle = 'Sign In';
require_once __DIR__ . '/../includes/functions.php';
start_session();
if (is_logged_in()) { header('Location: /pages/dashboard.php'); exit; }
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?= h($siteSettings['logo_path'] ?? '/public/img/logo.png') ?>" alt="HackathonAfrica" style="height: 48px; width: auto; margin: 0 auto; display: block;" class="mb-2">
            <h4 class="fw-700 mb-0">Welcome Back</h4>
            <p class="text-muted small">Sign in to continue learning</p>
        </div>

        <?php render_flash(); ?>

        <form action="/actions/login.php" method="POST" novalidate>
            <?= csrf_field() ?>
            <?php if (isset($_GET['redirect'])): ?>
            <input type="hidden" name="redirect" value="<?= h($_GET['redirect']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="you@example.com"
                       value="<?= h($_GET['email'] ?? '') ?>" required autofocus>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0" for="password">Password</label>
                    <a href="/pages/forgot_password.php" class="small">Forgot password?</a>
                </div>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Your password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Don't have an account? <a href="/pages/register.php">Create one free</a>
        </p>
    </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { pwd.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
