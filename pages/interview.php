<?php
$pageTitle = 'Coding Interview';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$unlocked = is_interview_unlocked($user['id']);
$session  = get_interview_session_for_user($user['id']);
$isAdmin  = is_admin();
$open     = is_interview_open();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 class="mb-1"><i class="bi bi-terminal me-2" style="color:var(--primary)"></i>Coding Interview</h1>
        <p class="mb-0">A proctored, hands-on assessment of your coding and debugging ability — the final step toward selection into HackathonAfrica.</p>
    </div>
</div>

<div class="container py-5">
    <?php render_flash(); ?>

    <?php if (!$unlocked && !$isAdmin): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4">
                <h4 class="fw-700 mb-3"><i class="bi bi-lock me-2 text-warning"></i>Pass the Final Exam First</h4>
                <p class="text-muted mb-0">The coding interview unlocks once you have completed all courses and passed the proctored Final Exam. Finish that step, then come back here.</p>
                <a href="/pages/qualifying_exam.php" class="btn btn-outline-primary mt-3 align-self-start">
                    <i class="bi bi-shield-check me-1"></i>Go to Final Exam
                </a>
            </div>
        </div>
    </div>

    <?php elseif ($session && $session['status'] === 'reviewed'): ?>
    <?php $decision = $session['review_decision']; ?>
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card p-5">
                <i class="bi bi-<?= $decision === 'selected' ? 'trophy-fill' : ($decision === 'rejected' ? 'x-circle' : 'hourglass-split') ?> mb-3"
                   style="font-size:3.5rem;color:var(--primary)"></i>
                <h2 class="fw-700 mb-2">
                    <?= $decision === 'selected' ? 'You Have Been Selected!' : ($decision === 'rejected' ? 'Interview Reviewed' : 'Under Review') ?>
                </h2>
                <p class="text-muted mb-3">Your interview has been reviewed by our team.</p>
                <?php if ($session['reviewer_notes']): ?>
                <div class="alert text-start" style="background:var(--surface-hover)">
                    <strong class="small text-muted d-block mb-1">Reviewer feedback</strong>
                    <?= nl2br(h($session['reviewer_notes'])) ?>
                </div>
                <?php endif; ?>
                <a href="/pages/dashboard.php" class="btn btn-primary mt-2">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <?php elseif ($session && $session['status'] === 'submitted'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card p-5">
                <i class="bi bi-check2-circle mb-3" style="font-size:3.5rem;color:var(--success)"></i>
                <h2 class="fw-700 mb-2">Submitted — Pending Review</h2>
                <p class="text-muted mb-1">Your interview was submitted on
                    <strong><?= date('M j, Y \a\t H:i', strtotime($session['submitted_at'])) ?></strong>.</p>
                <p class="text-muted">Our team will review your submissions and proctoring record, then confirm your selection decision. You cannot retake the interview.</p>
                <a href="/pages/dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <?php elseif ($session && $session['status'] === 'in_progress'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4">
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>You have an interview in progress. Resume it now — the timer keeps running.</div>
                <a href="/pages/interview_take.php" class="btn btn-warning btn-lg w-100">
                    <i class="bi bi-arrow-right-circle me-2"></i>Resume Interview
                </a>
            </div>
        </div>
    </div>

    <?php elseif (!$open && !$isAdmin): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4 text-center">
                <i class="bi bi-hourglass-split mb-3" style="font-size:3rem;color:var(--primary)"></i>
                <h4 class="fw-700 mb-2">The Coding Interview Isn't Open Yet</h4>
                <p class="text-muted mb-0">You're qualified to take it. The interview will become available once the HackathonAfrica team opens this stage — you'll be able to start it from here. Please check back soon.</p>
                <a href="/pages/dashboard.php" class="btn btn-outline-primary mt-4 align-self-center">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <?php if ($isAdmin && !$open): ?>
    <div class="alert alert-info"><i class="bi bi-eye me-1"></i><strong>Admin:</strong> the interview is currently <strong>closed</strong> for candidates. You can still start one to test. Open it for everyone from the Admin Dashboard.</div>
    <?php endif; ?>
    <div class="row justify-content-center g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="fw-700 mb-1">Interview Brief</h3>
                    <p class="text-muted mb-4">Hands-on tasks built from what you learned: write JavaScript, fix buggy code, and build real web features — forms, layouts and pages — live in the editor.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-3 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= INTERVIEW_CODING_COUNT ?></div>
                            <div class="small text-muted">Coding</div>
                        </div>
                        <div class="col-3 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= INTERVIEW_DEBUG_COUNT ?></div>
                            <div class="small text-muted">Debugging</div>
                        </div>
                        <div class="col-3 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= count(INTERVIEW_PROJECT_CATEGORIES) ?></div>
                            <div class="small text-muted">Applied web</div>
                        </div>
                        <div class="col-3 text-center p-3 rounded" style="background:var(--surface-hover)">
                            <div class="fw-700" style="font-size:1.5rem;color:var(--primary)"><?= INTERVIEW_TIME_LIMIT ?></div>
                            <div class="small text-muted">Minutes</div>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-4">
                        <strong><i class="bi bi-info-circle me-1"></i>How it works</strong>
                        <ul class="mb-0 mt-2 ps-3 small">
                            <li>Your tasks are drawn at random and are unique to you.</li>
                            <li>Coding &amp; debugging tasks: write JavaScript and use <strong>Run Tests</strong> to check sample cases.</li>
                            <li>Applied web tasks: build HTML/CSS/JS and use <strong>Run &amp; Check</strong> to see it live in the preview.</li>
                            <li>Your work autosaves continuously, and you get <strong>one</strong> attempt — no retakes.</li>
                        </ul>
                    </div>

                    <div class="alert mb-4" style="background:rgba(248,181,38,0.1);border:1px solid rgba(248,181,38,0.3)">
                        <strong><i class="bi bi-camera-video me-1" style="color:var(--primary)"></i>This interview is proctored.</strong>
                        Your camera will take periodic snapshots and tab-switching, copying and pasting are recorded for the reviewers. Work on your own, in a quiet, well-lit space.
                    </div>

                    <?php if ($isAdmin): ?>
                    <div class="alert alert-info"><i class="bi bi-eye me-1"></i><strong>Admin:</strong> eligibility<?= !$open ? ' and the open switch' : '' ?> are bypassed for you. Starting here creates a <strong>test run</strong> (flagged, kept out of the candidate review queue) — it does not open the interview for candidates.</div>
                    <?php endif; ?>

                    <form method="POST" action="/actions/start_interview.php">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-lg w-100"
                            onclick="return confirm('This interview is proctored and can only be taken once. Your camera will be activated. Start now?')">
                            <i class="bi bi-play-circle me-2"></i>Start Interview
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-3"><i class="bi bi-lightbulb me-1 text-warning"></i>Before you start</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">Check your camera is working.</li>
                        <li class="mb-2">Use a stable internet connection.</li>
                        <li class="mb-2">Close other tabs and apps.</li>
                        <li class="mb-2">Do not leave the interview tab.</li>
                        <li>Solve every task you can — partial solutions still count.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
