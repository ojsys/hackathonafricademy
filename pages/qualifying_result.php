<?php
$pageTitle = 'Exam Result';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$user = current_user();

$attemptId = filter_input(INPUT_GET, 'attempt_id', FILTER_VALIDATE_INT);
$attempt = $attemptId ? get_qualifying_attempt($attemptId) : null;

if (!$attempt || $attempt['user_id'] !== $user['id'] || !$attempt['completed_at']) {
    header('Location: /pages/qualifying_exam.php');
    exit;
}

$exam = db()->prepare('SELECT * FROM qualifying_exam WHERE id = ?');
$exam->execute([$attempt['exam_id']]);
$exam = $exam->fetch();

$answers = json_decode($attempt['answers_json'] ?? '{}', true) ?? [];
$mins    = $attempt['time_taken'] ? floor($attempt['time_taken'] / 60) : null;
$secs    = $attempt['time_taken'] ? $attempt['time_taken'] % 60 : null;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5" style="max-width:750px">

    <div class="card text-center p-5 mb-4">
        <?php if ($attempt['passed']): ?>
        <i class="bi bi-trophy-fill mb-3" style="font-size:4rem;color:var(--primary)"></i>
        <h2 class="fw-700 mb-1 text-success">You Passed!</h2>
        <p class="text-muted mb-0">Congratulations — you have qualified for the HackathonAfrica program.</p>
        <?php else: ?>
        <i class="bi bi-x-circle mb-3" style="font-size:4rem;color:var(--danger)"></i>
        <h2 class="fw-700 mb-1 text-danger">Not Passed</h2>
        <p class="text-muted mb-0">You scored below the pass mark. You may retake the exam.</p>
        <?php endif; ?>

        <div class="d-flex justify-content-center gap-4 mt-4 flex-wrap">
            <div class="text-center">
                <div class="fw-800" style="font-size:2.5rem;color:<?= $attempt['passed'] ? 'var(--success)' : 'var(--danger)' ?>"><?= $attempt['percentage'] ?>%</div>
                <div class="small text-muted">Your Score</div>
            </div>
            <div class="text-center">
                <div class="fw-800" style="font-size:2.5rem"><?= $exam['pass_mark'] ?>%</div>
                <div class="small text-muted">Pass Mark</div>
            </div>
            <div class="text-center">
                <div class="fw-800" style="font-size:2.5rem"><?= $attempt['score'] ?>/<?= $attempt['total_points'] ?></div>
                <div class="small text-muted">Points</div>
            </div>
            <?php if ($mins !== null): ?>
            <div class="text-center">
                <div class="fw-800" style="font-size:2.5rem"><?= $mins ?>:<?= str_pad($secs,2,'0',STR_PAD_LEFT) ?></div>
                <div class="small text-muted">Time Taken</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="/pages/dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
            <?php if (!$attempt['passed']): ?>
            <a href="/pages/qualifying_exam.php" class="btn btn-primary">Retake Exam</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($answers)): ?>
    <h5 class="fw-700 mb-3">Answer Review</h5>
    <?php foreach ($answers as $qId => $a): ?>
    <div class="card mb-3" style="border-left:3px solid <?= $a['right'] ? 'var(--success)' : 'var(--danger)' ?>">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <p class="fw-600 mb-0 small"><?= nl2br(h($a['text'])) ?></p>
                <i class="bi bi-<?= $a['right'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?> flex-shrink-0"></i>
            </div>
            <div class="row g-1">
                <?php foreach (($a['options'] ?? []) as $idx => $opt): ?>
                <?php
                    $isCorrect = (string)$idx === (string)$a['correct'];
                    $isUser    = (string)$idx === (string)$a['user'];
                    $cls = $isCorrect ? 'bg-success bg-opacity-10 border-success' : ($isUser && !$isCorrect ? 'bg-danger bg-opacity-10 border-danger' : '');
                ?>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2 small p-2 rounded border <?= $cls ?>">
                        <?php if ($isCorrect): ?>
                            <i class="bi bi-check-circle-fill text-success" style="font-size:.75rem;flex-shrink:0"></i>
                        <?php elseif ($isUser): ?>
                            <i class="bi bi-x-circle-fill text-danger" style="font-size:.75rem;flex-shrink:0"></i>
                        <?php else: ?>
                            <i class="bi bi-circle text-muted" style="font-size:.75rem;flex-shrink:0"></i>
                        <?php endif; ?>
                        <?= h($opt) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
