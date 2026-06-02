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
.cert-container { max-width: 960px; margin: 0 auto; }
.certificate {
    background: linear-gradient(135deg, #0D1117 0%, #151B23 50%, #1C2333 100%);
    border: 3px solid var(--primary);
    border-radius: 12px;
    padding: 56px 64px;
    position: relative;
    overflow: hidden;
    /* A4 landscape aspect ratio: 297 / 210 ≈ 1.414 */
    aspect-ratio: 297 / 210;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.certificate::before {
    content: '';
    position: absolute;
    top: 15px; left: 15px; right: 15px; bottom: 15px;
    border: 1px solid rgba(248,181,38,0.2);
    border-radius: 8px;
    pointer-events: none;
}
.cert-logo { height: 46px; width: auto; margin-bottom: 1.25rem; }
.cert-title {
    font-family: var(--font-heading);
    font-size: 1.9rem; font-weight: 800;
    background: linear-gradient(135deg, #F8B526 0%, #FFC03D 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text; margin-bottom: 0.4rem;
}
/* Certificate background is always dark — pin all text to explicit light
   colors so it stays legible regardless of the site's light/dark theme. */
.cert-subtitle { color: rgba(255,255,255,0.65); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; }
.cert-recipient { font-size: 2.2rem; font-weight: 700; font-family: var(--font-heading); color: #fff; margin-bottom: 0.4rem; }
.cert-course { font-size: 1.15rem; color: var(--primary); font-weight: 600; margin-bottom: 1.5rem; }
.cert-body-text { color: #c5cdd8; font-size: .9rem; margin-bottom: .2rem; }
.cert-details { display: flex; justify-content: space-between; border-top: 1px solid rgba(248,181,38,0.25); padding-top: 1.25rem; margin-top: 1.5rem; }
.cert-detail { text-align: center; }
.cert-detail-label { font-size: 0.65rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 0.1em; }
.cert-detail-value { font-size: 0.9rem; font-weight: 600; color: #ffffff; margin-top: 0.2rem; }
.cert-id { position: absolute; bottom: 22px; right: 28px; font-family: var(--font-mono); font-size: 0.65rem; color: rgba(255,255,255,0.45); }
@media print {
    @page { size: A4 landscape; margin: 0; }
    body { background: #0D1117 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .cert-container { max-width: 100%; padding: 10mm; }
    .certificate { border-radius: 0; box-shadow: none; aspect-ratio: unset; height: calc(100vh - 20mm); }
}
</style>

<div class="container py-5">
    <div class="cert-container">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="fw-700 mb-1" data-testid="cert-page-title">Your Certificate</h1>
                <p class="text-muted mb-0">You earned this! Share it with pride.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button id="btn-pdf" onclick="exportCert('pdf')" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                </button>
                <button id="btn-jpeg" onclick="exportCert('jpeg')" class="btn btn-outline-primary">
                    <i class="bi bi-image me-1"></i> Download JPEG
                </button>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Print
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

                <p class="cert-body-text mb-1">This certifies that</p>
                <div class="cert-recipient" data-testid="cert-name"><?= h($user['name']) ?></div>

                <p class="cert-body-text mb-1">has successfully completed</p>
                <div class="cert-course" data-testid="cert-course"><?= h($course['title']) ?></div>

                <p class="cert-body-text small mb-0">
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

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
function exportCert(format) {
    var el  = document.querySelector('.certificate');
    var btn = document.getElementById('btn-' + format);
    var orig = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Generating...';

    var recipientName = <?= json_encode($user['name']) ?>;
    var courseName    = <?= json_encode($course['title']) ?>;
    var slug = (recipientName + '-' + courseName).replace(/[^a-zA-Z0-9]+/g, '-').toLowerCase();
    var filename = 'certificate-' + slug;

    html2canvas(el, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#0D1117',
        logging: false
    }).then(function (canvas) {
        if (format === 'jpeg') {
            var link = document.createElement('a');
            link.download = filename + '.jpg';
            link.href = canvas.toDataURL('image/jpeg', 0.95);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            var jspdf   = window.jspdf;
            var pdf     = new jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
            var pageW   = pdf.internal.pageSize.getWidth();   // 297 mm
            var pageH   = pdf.internal.pageSize.getHeight();  // 210 mm
            var margin  = 8;
            var maxW    = pageW - margin * 2;
            var maxH    = pageH - margin * 2;
            var ratio   = Math.min(maxW / canvas.width, maxH / canvas.height);
            var imgW    = canvas.width  * ratio;
            var imgH    = canvas.height * ratio;
            var x       = (pageW - imgW) / 2;
            var y       = (pageH - imgH) / 2;
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', x, y, imgW, imgH);
            pdf.save(filename + '.pdf');
        }
        btn.disabled = false;
        btn.innerHTML = orig;
    }).catch(function (e) {
        console.error('exportCert:', e);
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
