<?php
/**
 * Open or close the participant feedback forms. When closed the public page
 * shows a notice and submissions are rejected; already-stored responses are
 * never affected.
 */
require_once __DIR__ . '/../../includes/feedback.php';
require_admin();
verify_csrf();

$open = ($_POST['open'] ?? '') === '1';
set_feedback_open($open);

set_flash('success', $open
    ? 'Feedback forms are now OPEN — participants can submit responses.'
    : 'Feedback forms are now CLOSED. No new responses will be accepted.');

header('Location: /admin/feedback.php');
exit;
