<?php
$pageTitle = 'Certificate';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$courseId = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if (!$courseId) { header('Location: /pages/dashboard.php'); exit; }

$course = get_course($courseId);
if (!$course) { header('Location: /pages/dashboard.php'); exit; }

// Must have passed final exam
if (!has_passed_final_exam($user['id'], $courseId)) {
    set_flash('warning', 'You must pass the final exam to receive a certificate.');
    header('Location: /pages/final_exam.php?course_id=' . $courseId);
    exit;
}

$exam = get_final_exam_for_course($courseId);
$attempt = $exam ? get_best_exam_attempt($user['id'], $exam['id']) : null;
$completedDate = $attempt ? date('F j, Y', strtotime($attempt['completed_at'])) : date('F j, Y');
$certId = strtoupper(substr(md5($user['id'] . '-' . $courseId . '-hackathon'), 0, 10));
$settings = get_site_settings();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.cert-container { max-width: 900px; margin: 0 auto; }
.certificate {
    background: linear-gradient(135deg, #0D1117 0%, #151B23 50%, #1C2333 100%);
    border: 3px solid var(--primary);
    border-radius: 12px;
    padding: 60px 50px;
    position: relative;
    overflow: hidden;
}
.certificate::before {
    content: '';
    position: absolute;
    top: 15px; left: 15px; right: 15px; bottom: 15px;
    border: 1px solid rgba(248,181,38,0.2);
    border-radius: 8px;
    pointer-events: none;
}
.cert-logo { height: 50px; width: auto; margin-bottom: 1.5rem; }
.cert-title {
    font-family: var(--font-heading);
    font-size: 2rem; font-weight: 800;
    background: linear-gradient(135deg, #F8B526 0%, #FFC03D 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text; margin-bottom: 0.5rem;
}
.cert-subtitle { color: var(--text-muted); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 2rem; }
.cert-recipient { font-size: 2.5rem; font-weight: 700; font-family: var(--font-heading); color: #fff; margin-bottom: 0.5rem; }
.cert-course { font-size: 1.25rem; color: var(--primary); font-weight: 600; margin-bottom: 2rem; }
.cert-details { display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 1.5rem; margin-top: 2rem; }
.cert-detail { text-align: center; }
.cert-detail-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; }
.cert-detail-value { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-top: 0.25rem; }
.cert-id { position: absolute; bottom: 25px; right: 30px; font-family: var(--font-mono); font-size: 0.7rem; color: var(--text-muted); }
@media print {
    body { background: #0D1117 !important; }
    .no-print { display: none !important; }
    .certificate { box-shadow: none; }
}
</style>

<div class="container py-5">
    <div class="cert-container">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="fw-700 mb-1" data-testid="cert-page-title">Your Certificate</h1>
                <p class="text-muted mb-0">You earned this! Share it with pride.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary" data-testid="print-cert">
                    <i class="bi bi-printer me-1"></i> Print / Save PDF
                </button>
                <a href="/pages/dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="certificate" data-testid="certificate">
            <div class="text-center">
                <img src="<?= h($settings['logo_path'] ?? '/public/img/logo.png') ?>" alt="AfricaPlan Foundation" class="cert-logo">
                <div class="cert-title">Certificate of Completion</div>
                <div class="cert-subtitle">HackathonAfrica Learning Platform</div>

                <p class="text-muted mb-1">This certifies that</p>
                <div class="cert-recipient" data-testid="cert-name"><?= h($user['name']) ?></div>

                <p class="text-muted mb-1">has successfully completed</p>
                <div class="cert-course" data-testid="cert-course"><?= h($course['title']) ?></div>

                <p class="text-muted small mb-0">
                    including all modules, exercises, and the final assessment
                    <?php if ($attempt): ?>
                    with a score of <strong style="color: var(--primary)"><?= $attempt['score'] ?>%</strong>
                    <?php endif; ?>
                </p>
            </div>

            <div class="cert-details">
                <div class="cert-detail">
                    <div class="cert-detail-label">Date Issued</div>
                    <div class="cert-detail-value"><?= $completedDate ?></div>
                </div>
                <div class="cert-detail">
                    <div class="cert-detail-label">Certificate ID</div>
                    <div class="cert-detail-value"><?= $certId ?></div>
                </div>
                <div class="cert-detail">
                    <div class="cert-detail-label">Issued By</div>
                    <div class="cert-detail-value">AfricaPlan Foundation</div>
                </div>
            </div>

            <div class="cert-id">CERT-<?= $certId ?></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
