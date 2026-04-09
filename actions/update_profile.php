<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/profile.php');
    exit;
}

verify_csrf();
$user = current_user();
$section = $_POST['section'] ?? 'account';

if ($section === 'account') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');

    if (strlen($name) < 2 || strlen($name) > 100) {
        set_flash('error', 'Name must be between 2 and 100 characters.');
        header('Location: /pages/profile.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
        header('Location: /pages/profile.php');
        exit;
    }
    if ($email !== $user['email']) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            set_flash('error', 'That email is already in use by another account.');
            header('Location: /pages/profile.php');
            exit;
        }
    }

    $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
    $stmt->execute([$name, $email, $phone ?: null, $user['id']]);

    set_flash('success', 'Account information updated.');

} elseif ($section === 'background') {
    $country        = trim($_POST['country'] ?? '');
    $city           = trim($_POST['city'] ?? '');
    $educationLevel = trim($_POST['education_level'] ?? '');
    $yearsExp       = trim($_POST['years_experience'] ?? '');
    $githubUrl      = trim($_POST['github_url'] ?? '');
    $linkedinUrl    = trim($_POST['linkedin_url'] ?? '');
    $bio            = trim($_POST['bio'] ?? '');
    $howHeard       = trim($_POST['how_heard'] ?? '');

    if ($githubUrl && !filter_var($githubUrl, FILTER_VALIDATE_URL)) {
        set_flash('error', 'Please enter a valid GitHub URL.');
        header('Location: /pages/profile.php');
        exit;
    }
    if ($linkedinUrl && !filter_var($linkedinUrl, FILTER_VALIDATE_URL)) {
        set_flash('error', 'Please enter a valid LinkedIn URL.');
        header('Location: /pages/profile.php');
        exit;
    }
    if (strlen($bio) > 500) {
        set_flash('error', 'Bio must not exceed 500 characters.');
        header('Location: /pages/profile.php');
        exit;
    }

    $stmt = db()->prepare('
        UPDATE users SET
            country = ?, city = ?, education_level = ?, years_experience = ?,
            github_url = ?, linkedin_url = ?, bio = ?, how_heard = ?
        WHERE id = ?
    ');
    $stmt->execute([
        $country ?: null, $city ?: null, $educationLevel ?: null, $yearsExp ?: null,
        $githubUrl ?: null, $linkedinUrl ?: null, $bio ?: null, $howHeard ?: null,
        $user['id'],
    ]);

    set_flash('success', 'Background information updated.');
}

header('Location: /pages/profile.php');
exit;
