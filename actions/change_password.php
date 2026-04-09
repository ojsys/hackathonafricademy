<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/profile.php');
    exit;
}

verify_csrf();
$user = current_user();

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!password_verify($current, $user['password'])) {
    set_flash('error', 'Current password is incorrect.');
    header('Location: /pages/profile.php');
    exit;
}
if (strlen($new) < 8) {
    set_flash('error', 'New password must be at least 8 characters.');
    header('Location: /pages/profile.php');
    exit;
}
if ($new !== $confirm) {
    set_flash('error', 'New passwords do not match.');
    header('Location: /pages/profile.php');
    exit;
}

$hash = password_hash($new, PASSWORD_BCRYPT);
$stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$hash, $user['id']]);

set_flash('success', 'Password changed successfully.');
header('Location: /pages/profile.php');
exit;
