<?php
$pageTitle = 'Set New Password';
require_once __DIR__ . '/../includes/functions.php';
start_session();
if (is_logged_in()) { header('Location: /pages/dashboard.php'); exit; }

$token = trim($_GET['token'] ?? '');
$valid = false;
$user  = null;

if ($token !== '') {
    $stmt = db()->prepare('SELECT id, name, email FROM users WHERE reset_token = ? AND reset_expires > ?');
    $stmt->execute([$token, date('Y-m-d H:i:s')]);
    $user  = $stmt->fetch();
    $valid = (bool)$user;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?= h($siteSettings['logo_path'] ?? '/public/img/logo.png') ?>" alt="HackathonAfrica" style="height: 48px; width: auto; margin: 0 auto; display: block;" class="mb-2">
            <h4 class="fw-700 mb-0">Set New Password</h4>
            <p class="text-muted small">Choose a strong password for your account</p>
        </div>

        <?php render_flash(); ?>

        <?php if (!$valid): ?>
            <div class="alert alert-danger">
                This password reset link is invalid or has expired. Reset links are only valid for 1 hour.
            </div>
            <p class="text-center mt-3">
                <a href="/pages/forgot_password.php" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-envelope me-2"></i>Request a New Reset Link
                </a>
            </p>
        <?php else: ?>
            <form action="/actions/reset_password.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <div class="mb-3">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password"
                           placeholder="At least 8 characters" required minlength="8" autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           placeholder="Repeat your new password" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-lock me-2"></i>Set New Password
                </button>
            </form>
        <?php endif; ?>

        <p class="text-center text-muted small mt-4 mb-0">
            Remember your password? <a href="/pages/login.php">Sign in</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
