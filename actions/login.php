<?php
require_once __DIR__ . '/../includes/functions.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/login.php');
    exit;
}

verify_csrf();

$email    = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? '';

if (!$email || !$password) {
    set_flash('error', 'Please enter your email and password.');
    header('Location: /pages/login.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    set_flash('error', 'Invalid email or password. Please try again.');
    header('Location: /pages/login.php?email=' . urlencode($email));
    exit;
}

if (!$user['is_active']) {
    set_flash('error', 'Your account has been deactivated. Please contact support.');
    header('Location: /pages/login.php');
    exit;
}

// Regenerate session ID on login (session fixation prevention)
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];

// Safe redirect: only allow relative paths
$safePaths = ['/pages/', '/admin/'];
$validRedirect = false;
if ($redirect) {
    foreach ($safePaths as $prefix) {
        if (strpos($redirect, $prefix) === 0) {
            $validRedirect = true;
            break;
        }
    }
}

if ($validRedirect) {
    header('Location: ' . $redirect);
} elseif (in_array($user['role'], ['admin', 'superadmin'])) {
    header('Location: /admin/index.php');
} else {
    header('Location: /pages/dashboard.php');
}
exit;
