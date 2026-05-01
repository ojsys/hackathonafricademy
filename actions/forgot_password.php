<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/forgot_password.php');
    exit;
}

verify_csrf();

$email = trim(strtolower($_POST['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please enter a valid email address.');
    header('Location: /pages/forgot_password.php');
    exit;
}

// Always show success to prevent email enumeration
$stmt = db()->prepare('SELECT id, name FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $stmt = db()->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $stmt->execute([$token, $expires, $user['id']]);

    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $resetUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/pages/reset_password.php?token=' . $token;
    email_password_reset($email, $user['name'], $resetUrl);
}

set_flash('success', 'If an account with that email exists, a password reset link has been sent.');
header('Location: /pages/forgot_password.php');
exit;
