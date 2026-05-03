<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/functions.php';
start_session();
if (is_logged_in()) { header('Location: /pages/dashboard.php'); exit; }
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?= h($siteSettings['logo_path'] ?? '/public/img/logo.png') ?>" alt="HackathonAfrica" style="height: 48px; width: auto; margin: 0 auto; display: block;" class="mb-2">
            <h4 class="fw-700 mb-0">Reset Password</h4>
            <p class="text-muted small">Enter your email to receive a reset link</p>
        </div>

        <?php render_flash(); ?>

        <form action="/actions/forgot_password.php" method="POST" id="forgotForm">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="you@example.com" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                <span id="btnDefault"><i class="bi bi-envelope me-2"></i>Send Reset Link</span>
                <span id="btnLoading" style="display:none;">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Sending&hellip;
                </span>
            </button>
        </form>

        <script>
        document.getElementById('forgotForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            document.getElementById('btnDefault').style.display = 'none';
            document.getElementById('btnLoading').style.display = 'inline';
            btn.disabled = true;
        });
        </script>

        <p class="text-center text-muted small mt-4 mb-0">
            Remember your password? <a href="/pages/login.php">Sign in</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
