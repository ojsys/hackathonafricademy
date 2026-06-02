<?php
/**
 * Open or close the coding interview for all candidates. Default state is
 * closed; candidates cannot start the interview until an admin opens it.
 * In-progress sessions are never interrupted by closing it.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$open = ($_POST['open'] ?? '') === '1';
set_interview_open($open);

set_flash('success', $open
    ? 'The coding interview is now OPEN for all qualified candidates.'
    : 'The coding interview is now CLOSED. Candidates can no longer start it.');

$redirect = $_POST['redirect'] ?? '/admin/index.php';
// Only allow internal redirects.
if (!preg_match('#^/admin/[a-z_]+\.php#', $redirect)) $redirect = '/admin/index.php';
header('Location: ' . $redirect);
exit;
