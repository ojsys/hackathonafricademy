<?php
/**
 * Admin action: run the ranking engine, shortlist top N candidates,
 * and optionally send bootcamp invitation emails.
 */
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/mailer.php';
require_admin();
verify_csrf();

$action = $_POST['action'] ?? '';
$limit  = (int)(get_setting('shortlist_limit', '100'));

if ($action === 'run_ranking') {
    // Recalculate composite score for every registered student
    $students = db()->query('SELECT id FROM users WHERE role = "student"')->fetchAll(PDO::FETCH_COLUMN);
    $updated = 0;
    foreach ($students as $uid) {
        create_or_update_candidate_review((int)$uid);
        $updated++;
    }
    set_flash('success', "Scores recalculated for $updated candidates.");

} elseif ($action === 'shortlist') {
    // Clear previous shortlist
    db()->exec("UPDATE candidate_reviews SET shortlisted = 0, shortlisted_at = NULL WHERE shortlisted = 1");

    // Top N by composite_score among eligible candidates
    $stmt = db()->prepare("
        UPDATE candidate_reviews SET shortlisted = 1, shortlisted_at = CURRENT_TIMESTAMP
        WHERE id IN (
            SELECT id FROM candidate_reviews
            WHERE eligibility_status = 'eligible'
            ORDER BY composite_score DESC
            LIMIT ?
        )
    ");
    $stmt->execute([$limit]);
    $count = $stmt->rowCount();
    set_flash('success', "$count candidates shortlisted (top $limit by composite score).");

} elseif ($action === 'invite') {
    // Send bootcamp invitation to all shortlisted candidates who haven't been invited yet
    $shortlisted = db()->query("
        SELECT u.email, u.name, cr.id as cr_id
        FROM candidate_reviews cr
        JOIN users u ON u.id = cr.user_id
        WHERE cr.shortlisted = 1
          AND (cr.bootcamp_status = 'not_invited' OR cr.bootcamp_status IS NULL)
    ")->fetchAll(PDO::FETCH_ASSOC);

    $details = [
        'dates'         => get_setting('bootcamp_dates', 'TBA'),
        'location'      => 'HackathonAfrica Lab, Enugu',
        'rsvp_deadline' => get_setting('rsvp_deadline', 'TBA'),
        'rsvp_url'      => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/pages/dashboard.php',
    ];

    $sent = 0;
    $updateStmt = db()->prepare("UPDATE candidate_reviews SET bootcamp_status = 'invited', invited_at = CURRENT_TIMESTAMP WHERE id = ?");
    foreach ($shortlisted as $c) {
        if (email_bootcamp_invitation($c['email'], $c['name'], $details)) {
            $updateStmt->execute([$c['cr_id']]);
            $sent++;
        }
    }
    set_flash('success', "Bootcamp invitations sent to $sent candidates.");

} elseif ($action === 'notify_not_selected') {
    // Send appreciation email to all candidates NOT shortlisted who have completed all courses
    $notSelected = db()->query("
        SELECT u.email, u.name
        FROM candidate_reviews cr
        JOIN users u ON u.id = cr.user_id
        WHERE cr.shortlisted = 0
          AND cr.eligibility_status IN ('eligible','needs_review')
    ")->fetchAll(PDO::FETCH_ASSOC);

    $sent = 0;
    foreach ($notSelected as $c) {
        if (email_not_shortlisted($c['email'], $c['name'])) $sent++;
    }
    set_flash('success', "Appreciation emails sent to $sent candidates.");

} else {
    set_flash('error', 'Unknown action.');
}

header('Location: /admin/candidates.php');
exit;
