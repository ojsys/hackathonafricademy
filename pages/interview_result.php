<?php
$pageTitle = 'Interview Submitted';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$session = get_interview_session_for_user($user['id']);
if (!$session) { header('Location: /pages/interview.php'); exit; }
// If still open somehow, send them back to finish.
if ($session['status'] === 'in_progress') { header('Location: /pages/interview_take.php'); exit; }

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <?php render_flash(); ?>
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card p-5">
                <?php if ($session['status'] === 'reviewed'): ?>
                <?php $d = $session['review_decision']; ?>
                <i class="bi bi-<?= $d === 'selected' ? 'trophy-fill' : ($d === 'rejected' ? 'x-circle' : 'hourglass-split') ?> mb-3" style="font-size:3.5rem;color:var(--primary)"></i>
                <h2 class="fw-700 mb-2"><?= $d === 'selected' ? 'You Have Been Selected!' : 'Interview Reviewed' ?></h2>
                <?php if ($session['reviewer_notes']): ?>
                <div class="alert text-start mt-2" style="background:var(--surface-hover)">
                    <strong class="small text-muted d-block mb-1">Reviewer feedback</strong>
                    <?= nl2br(h($session['reviewer_notes'])) ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <i class="bi bi-check2-circle mb-3" style="font-size:3.5rem;color:var(--success)"></i>
                <h2 class="fw-700 mb-2">Interview Submitted</h2>
                <p class="text-muted mb-1">Submitted on <strong><?= date('M j, Y \a\t H:i', strtotime($session['submitted_at'])) ?></strong>.</p>
                <p class="text-muted mb-0">Our team will review your submissions and proctoring record and confirm your selection decision. You cannot retake the interview.</p>
                <?php endif; ?>
                <a href="/pages/dashboard.php" class="btn btn-primary mt-4">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
