<?php
require_once __DIR__ . '/../includes/functions.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/forgot_password.php');
    exit;
}

verify_csrf();

$token   = trim($_POST['token'] ?? '');
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($token === '') {
    set_flash('error', 'Invalid reset request.');
    header('Location: /pages/forgot_password.php');
    exit;
}

$stmt = db()->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_expires > ?');
$stmt->execute([$token, date('Y-m-d H:i:s')]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'This reset link is invalid or has expired. Please request a new one.');
    header('Location: /pages/forgot_password.php');
    exit;
}

if (strlen($new) < 8) {
    set_flash('error', 'Password must be at least 8 characters.');
    header('Location: /pages/reset_password.php?token=' . urlencode($token));
    exit;
}

if ($new !== $confirm) {
    set_flash('error', 'Passwords do not match.');
    header('Location: /pages/reset_password.php?token=' . urlencode($token));
    exit;
}

$hash = password_hash($new, PASSWORD_BCRYPT);
$stmt = db()->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
$stmt->execute([$hash, $user['id']]);

set_flash('success', 'Your password has been reset. You can now sign in with your new password.');
header('Location: /pages/login.php');
exit;
