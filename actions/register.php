<?php
require_once __DIR__ . '/../includes/functions.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/register.php');
    exit;
}

verify_csrf();

$name             = trim($_POST['name'] ?? '');
$email            = trim(strtolower($_POST['email'] ?? ''));
$password         = $_POST['password'] ?? '';
$confirm          = $_POST['password_confirm'] ?? '';
$phone            = trim($_POST['phone'] ?? '');
$country          = trim($_POST['country'] ?? '');
$city             = trim($_POST['city'] ?? '');
$educationLevel   = trim($_POST['education_level'] ?? '');
$yearsExp         = trim($_POST['years_experience'] ?? '');
$githubUrl        = trim($_POST['github_url'] ?? '');
$linkedinUrl      = trim($_POST['linkedin_url'] ?? '');
$bio              = trim($_POST['bio'] ?? '');
$howHeard         = trim($_POST['how_heard'] ?? '');

// Validate required fields
$errors = [];
if (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}
if (strlen($phone) < 7) {
    $errors[] = 'Please enter a valid phone number.';
}
if (empty($country)) {
    $errors[] = 'Please select your country.';
}
if (strlen($city) < 2) {
    $errors[] = 'Please enter your city.';
}
if (empty($educationLevel)) {
    $errors[] = 'Please select your education level.';
}
if (empty($yearsExp)) {
    $errors[] = 'Please select your coding experience level.';
}
if (empty($howHeard)) {
    $errors[] = 'Please tell us how you heard about HackathonAfrica.';
}
if (strlen($bio) < 20) {
    $errors[] = 'Bio must be at least 20 characters.';
}
if (strlen($bio) > 500) {
    $errors[] = 'Bio must not exceed 500 characters.';
}
// Validate optional URLs if provided
if ($githubUrl && !filter_var($githubUrl, FILTER_VALIDATE_URL)) {
    $errors[] = 'Please enter a valid GitHub URL.';
}
if ($linkedinUrl && !filter_var($linkedinUrl, FILTER_VALIDATE_URL)) {
    $errors[] = 'Please enter a valid LinkedIn URL.';
}

if ($errors) {
    set_flash('error', implode(' ', $errors));
    header('Location: /pages/register.php');
    exit;
}

// Check duplicate email
$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    set_flash('error', 'An account with that email already exists. Please log in.');
    header('Location: /pages/login.php?email=' . urlencode($email));
    exit;
}

// Create user with all profile fields
try {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $skillLevel = trim($_POST['skill_level'] ?? 'beginner');
    if (!in_array($skillLevel, ['beginner', 'intermediate', 'advanced'])) $skillLevel = 'beginner';

    $gender = in_array($_POST['gender'] ?? '', ['male','female','other']) ? $_POST['gender'] : null;

    $stmt = db()->prepare('
        INSERT INTO users
            (name, email, password, role, phone, country, city, education_level,
             years_experience, github_url, linkedin_url, bio, how_heard, skill_level, gender)
        VALUES (?, ?, ?, "student", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $name, $email, $hash,
        $phone, $country, $city, $educationLevel,
        $yearsExp, $githubUrl ?: null, $linkedinUrl ?: null, $bio, $howHeard, $skillLevel, $gender,
    ]);
    $userId = db()->lastInsertId();

    // Auto-enroll in all published courses
    $courses = get_all_published_courses();
    foreach ($courses as $c) {
        enroll_user($userId, $c['id']);
    }

    // Log in immediately
    $_SESSION['user_id'] = $userId;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Send welcome email (non-blocking — failure doesn't stop registration)
    try {
        require_once __DIR__ . '/../includes/mailer.php';
        $loginUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/pages/login.php';
        email_welcome($email, $name, $loginUrl);
    } catch (Throwable $e) {
        log_error($e); // Using our custom log_error function
    }

    set_flash('success', 'Welcome, ' . $name . '! Your account is ready. Start learning below.');
    header('Location: /pages/dashboard.php');
    exit;

} catch (Throwable $e) {
    log_error($e);
    set_flash('error', 'A system error occurred during registration. Please try again.');
    header('Location: /pages/register.php');
    exit;
}
