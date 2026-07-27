<?php
/**
 * Handle a participant feedback submission (Feeding / Accommodation / Training).
 *
 * Public — no login required. Submissions may be anonymous: when the participant
 * ticks "Submit anonymously" neither their name nor their user id is stored.
 */
require_once __DIR__ . '/../includes/feedback.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/feedback.php');
    exit;
}

$category = $_POST['category'] ?? '';

// verify_csrf() treats a session with no token at all as a match (''===''), so
// check the session really issued one. A participant whose session expired gets
// their answers handed back rather than an error page.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['feedback_old'] = [
        'category'      => feedback_form($category) ? $category : 'feeding',
        'anonymous'     => !empty($_POST['anonymous']),
        'name'          => trim($_POST['name'] ?? ''),
        'feedback_date' => trim($_POST['feedback_date'] ?? ''),
        'q'             => feedback_form($category) ? feedback_validate($category, $_POST)[0] : [],
    ];
    set_flash('error', 'Your session expired before the form was submitted. Please check your answers and submit again.');
    header('Location: /pages/feedback.php' . (feedback_form($category) ? '?tab=' . urlencode($category) : ''));
    exit;
}

verify_csrf();
if (!feedback_form($category)) {
    set_flash('error', 'Unknown feedback form.');
    header('Location: /pages/feedback.php');
    exit;
}

$redirect = '/pages/feedback.php?tab=' . urlencode($category);

if (!feedback_is_open()) {
    set_flash('error', 'Feedback collection is currently closed.');
    header('Location: ' . $redirect);
    exit;
}

// Honeypot: bots fill every field. Pretend success, store nothing.
if (trim($_POST['website'] ?? '') !== '') {
    header('Location: /pages/feedback.php?submitted=' . urlencode($category));
    exit;
}

$anonymous = !empty($_POST['anonymous']);
$name      = trim($_POST['name'] ?? '');
$date      = trim($_POST['feedback_date'] ?? '');

[$answers, $errors] = feedback_validate($category, $_POST);

if (!$anonymous && $name === '') {
    $errors['name'] = 'Enter your name, or tick "Submit anonymously".';
}
$name = mb_substr($name, 0, 150);

// Fall back to today rather than rejecting an odd/blank date.
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
    $date = date('Y-m-d');
}

if ($errors) {
    $_SESSION['feedback_errors'] = $errors;
    $_SESSION['feedback_old'] = [
        'category'      => $category,
        'anonymous'     => $anonymous,
        'name'          => $name,
        'feedback_date' => $date,
        'q'             => $answers,
    ];
    header('Location: ' . $redirect);
    exit;
}

$userId = $anonymous ? null : (current_user()['id'] ?? null);

try {
    feedback_save($category, $answers, $name, $date, $anonymous, $userId);
} catch (Throwable $e) {
    // log_error() renders the 500 page and exits, so log directly and keep the
    // participant on the form with their submission intact.
    error_log('Feedback save failed: ' . $e->getMessage());
    $_SESSION['feedback_old'] = [
        'category'      => $category,
        'anonymous'     => $anonymous,
        'name'          => $name,
        'feedback_date' => $date,
        'q'             => $answers,
    ];
    $_SESSION['feedback_errors'] = [];
    set_flash('error', 'Sorry — your feedback could not be saved. Please try again.');
    header('Location: ' . $redirect);
    exit;
}

header('Location: /pages/feedback.php?submitted=' . urlencode($category));
exit;
