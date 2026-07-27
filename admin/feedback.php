<?php
/**
 * Admin → Participant Feedback.
 * Response volume, rating averages and per-question breakdowns for the three
 * feedback forms, plus the full response log and Excel/CSV export.
 */
$pageTitle = 'Participant Feedback';
require_once __DIR__ . '/../includes/feedback.php';
require_admin();

$forms = feedback_forms();

$category = $_GET['category'] ?? 'all';
if ($category !== 'all' && !isset($forms[$category])) $category = 'all';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$total   = feedback_count($category);
$totalPages = max(1, (int)ceil($total / $perPage));
$page    = min($page, $totalPages);
$responses = feedback_get_responses($category, $perPage, ($page - 1) * $perPage);

$counts = ['all' => feedback_count('all')];
foreach ($forms as $cat => $_f) $counts[$cat] = feedback_count($cat);

$anonCount = (int)db()->query('SELECT COUNT(*) FROM feedback_responses WHERE is_anonymous = 1')->fetchColumn();
$last = db()->query('SELECT MAX(submitted_at) FROM feedback_responses')->fetchColumn();

// Overall satisfaction = mean of every rating answer across all forms (out of 5).
$overallAvg = db()->query(
    "SELECT AVG(CAST(a.answer_value AS REAL)) FROM feedback_answers a
     JOIN feedback_responses r ON r.id = a.response_id
     WHERE a.answer_value IN ('1','2','3','4','5')"
)->fetchColumn();

$isOpen = feedback_is_open();

// Analysed categories: the selected one, or all three when viewing "All".
$analysed = $category === 'all' ? array_keys($forms) : [$category];

$baseQs = fn(array $over = []) => '?' . http_build_query(array_merge(['category' => $category], $over));

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.fb-meter { height: 8px; background: var(--bg); border-radius: 4px; overflow: hidden; }
.fb-meter > span { display: block; height: 100%; background: var(--primary); }
.fb-answer-grid { display: grid; grid-template-columns: minmax(200px, 320px) 1fr; gap: .35rem 1.25rem; font-size: .85rem; }
.fb-answer-grid dt { color: var(--text-muted); font-weight: 500; }
.fb-answer-grid dd { color: var(--text-primary); margin: 0; white-space: pre-wrap; }
@media (max-width: 700px) { .fb-answer-grid { grid-template-columns: 1fr; gap: 0 0; } .fb-answer-grid dd { margin-bottom: .6rem; } }
.fb-pill { display: inline-block; padding: .1rem .5rem; border-radius: 999px; font-size: .72rem; border: 1px solid var(--border); color: var(--text-secondary); }
</style>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
            <h1 class="admin-page-title mb-0">Participant Feedback</h1>
            <div class="d-flex gap-2 flex-wrap">
                <a href="/pages/feedback.php" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View form
                </a>
                <form method="POST" action="/actions/admin/toggle_feedback.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="open" value="<?= $isOpen ? '0' : '1' ?>">
                    <button class="btn btn-sm <?= $isOpen ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                        <i class="bi bi-<?= $isOpen ? 'lock' : 'unlock' ?> me-1"></i><?= $isOpen ? 'Close feedback' : 'Open feedback' ?>
                    </button>
                </form>
            </div>
        </div>
        <p class="text-muted">
            Camp feedback submitted by participants — feeding, accommodation and training delivery.
            Responses may be anonymous, so some have no name attached.
            <?= $isOpen ? '' : '<span class="text-danger">Collection is currently closed.</span>' ?>
        </p>

        <?php render_flash(); ?>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <?php
            $cards = [
                ['Total responses', number_format($counts['all']), 'chat-square-text', 'blue'],
                ['🍽️ Feeding', number_format($counts['feeding']), 'egg-fried', 'orange'],
                ['🏠 Accommodation', number_format($counts['accommodation']), 'house', 'purple'],
                ['💻 Training', number_format($counts['training']), 'laptop', 'green'],
                ['Anonymous', number_format($anonCount) . ($counts['all'] ? ' (' . round($anonCount / $counts['all'] * 100) . '%)' : ''), 'incognito', 'blue'],
                ['Named', number_format($counts['all'] - $anonCount), 'person-badge', 'blue'],
                ['Avg rating (all)', $overallAvg !== null && $overallAvg !== false ? round((float)$overallAvg, 2) . ' / 5' : '—', 'star', 'orange'],
                ['Last response', $last ? date('M j, H:i', strtotime($last)) : '—', 'clock-history', 'green'],
            ];
            foreach ($cards as [$label, $value, $icon, $color]): ?>
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon <?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></div>
                        <div>
                            <div class="fw-700" style="font-size:1.2rem;line-height:1"><?= h($value) ?></div>
                            <div class="text-muted small"><?= $label ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Category filter -->
        <ul class="nav nav-pills mb-4 gap-2">
            <li class="nav-item">
                <a class="nav-link <?= $category === 'all' ? 'active' : '' ?>" href="?category=all">
                    All <span class="badge bg-secondary ms-1"><?= $counts['all'] ?></span>
                </a>
            </li>
            <?php foreach ($forms as $cat => $form): ?>
            <li class="nav-item">
                <a class="nav-link <?= $category === $cat ? 'active' : '' ?>" href="?category=<?= h($cat) ?>">
                    <?= $form['icon'] ?> <?= h($form['label']) ?>
                    <span class="badge bg-secondary ms-1"><?= $counts[$cat] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Question breakdowns -->
        <?php foreach ($analysed as $cat):
            $form = $forms[$cat];
            $averages = feedback_rating_averages($cat);
            $choiceFields = array_filter(feedback_fields($cat), fn($d) => in_array($d['type'], ['emoji', 'checkbox'], true));
            if ($counts[$cat] === 0) continue;
        ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="fw-700 mb-3"><?= $form['icon'] ?> <?= h($form['title']) ?>
                    <span class="text-muted fw-400" style="font-size:.85rem">(<?= $counts[$cat] ?> responses)</span>
                </h5>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="text-muted small text-uppercase fw-600 mb-2">Rating averages</div>
                        <?php foreach ($averages as $key => $a): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between gap-2 small mb-1">
                                    <span><?= h($a['label']) ?></span>
                                    <span class="fw-700"><?= $a['avg'] !== null ? $a['avg'] . '/5' : '—' ?></span>
                                </div>
                                <div class="fb-meter"><span style="width:<?= $a['avg'] !== null ? round($a['avg'] / 5 * 100) : 0 ?>%"></span></div>
                                <div class="text-muted" style="font-size:.72rem"><?= $a['count'] ?> answer<?= $a['count'] === 1 ? '' : 's' ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$averages): ?><p class="text-muted small">No rating questions.</p><?php endif; ?>
                    </div>

                    <div class="col-lg-6">
                        <div class="text-muted small text-uppercase fw-600 mb-2">Answer breakdown</div>
                        <?php foreach ($choiceFields as $key => $def):
                            $dist = feedback_answer_distribution($cat, $key);
                            if (!$dist) continue;
                            // Multi-select answers are stored joined — split them back out to count each option.
                            $tally = [];
                            foreach ($dist as $d) {
                                $parts = $def['type'] === 'checkbox' ? explode('; ', (string)$d['answer_value']) : [(string)$d['answer_value']];
                                foreach ($parts as $p) {
                                    if ($p === '') continue;
                                    $tally[$p] = ($tally[$p] ?? 0) + (int)$d['n'];
                                }
                            }
                            arsort($tally);
                            $max = max($tally ?: [1]);
                        ?>
                            <div class="mb-3">
                                <div class="small mb-1"><?= h($def['label']) ?></div>
                                <?php foreach ($tally as $value => $n):
                                    $label = $def['type'] === 'emoji'
                                        ? (($def['options'][$value][0] ?? '') . ' ' . ($def['options'][$value][1] ?? $value))
                                        : ($def['options'][$value] ?? $value);
                                ?>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="small text-muted" style="min-width:150px"><?= h($label) ?></span>
                                        <div class="fb-meter flex-grow-1"><span style="width:<?= round($n / $max * 100) ?>%"></span></div>
                                        <span class="small fw-600" style="width:28px;text-align:right"><?= $n ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$choiceFields): ?><p class="text-muted small">No choice questions.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Responses -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="fw-700 mb-0"><i class="bi bi-list-ul me-2"></i>Responses
                        <span class="text-muted fw-400" style="font-size:.85rem">(<?= number_format($total) ?>)</span>
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($category === 'all'): ?>
                            <?php foreach ($forms as $cat => $form): ?>
                                <a href="/actions/admin/export_feedback.php?category=<?= h($cat) ?>"
                                   class="btn btn-success btn-sm" title="Download <?= h($form['label']) ?> responses for Excel">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i><?= h($form['label']) ?>
                                </a>
                            <?php endforeach; ?>
                            <a href="/actions/admin/export_feedback.php?category=all" class="btn btn-outline-success btn-sm"
                               title="All responses in one combined sheet">
                                <i class="bi bi-download me-1"></i>All (combined)
                            </a>
                        <?php else: ?>
                            <a href="/actions/admin/export_feedback.php?category=<?= h($category) ?>" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export to Excel (CSV)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr>
                            <th style="width:1%"></th>
                            <th>Submitted</th>
                            <th>Form</th>
                            <th>Respondent</th>
                            <th>Date of experience</th>
                            <th>Ratings</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($responses as $r):
                            $form = $forms[$r['category']] ?? null;
                            if (!$form) continue;
                            $fields = feedback_fields($r['category']);
                            $ratings = [];
                            foreach ($fields as $k => $d) {
                                if ($d['type'] === 'rating' && isset($r['answers'][$k]) && $r['answers'][$k] !== '') {
                                    $ratings[] = (int)$r['answers'][$k];
                                }
                            }
                            $avg = $ratings ? round(array_sum($ratings) / count($ratings), 1) : null;
                        ?>
                            <tr>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-1" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#fb-r<?= (int)$r['id'] ?>"
                                            aria-label="Show full response">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </td>
                                <td class="small text-muted"><?= h(date('M j, Y H:i', strtotime($r['submitted_at']))) ?></td>
                                <td class="small"><?= $form['icon'] ?> <?= h($form['label']) ?></td>
                                <td class="small">
                                    <?php if ((int)$r['is_anonymous'] === 1): ?>
                                        <span class="fb-pill"><i class="bi bi-incognito me-1"></i>Anonymous</span>
                                    <?php else: ?>
                                        <span class="fw-600"><?= h($r['respondent_name'] ?? $r['user_name'] ?? 'Unnamed') ?></span>
                                        <?php if (!empty($r['user_email'])): ?>
                                            <div class="text-muted" style="font-size:.72rem"><?= h($r['user_email']) ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= h($r['feedback_date'] ?? '—') ?></td>
                                <td class="small">
                                    <?= $avg !== null ? '<span class="fw-700">' . $avg . '</span>/5 <span class="text-muted">(' . count($ratings) . ')</span>' : '<span class="text-muted">—</span>' ?>
                                </td>
                            </tr>
                            <tr class="collapse" id="fb-r<?= (int)$r['id'] ?>">
                                <td colspan="6" style="background:var(--bg)">
                                    <dl class="fb-answer-grid mb-0 py-2">
                                        <?php foreach ($fields as $k => $d):
                                            $val = feedback_display_answer($d, $r['answers'][$k] ?? '');
                                            if ($val === '') continue;
                                        ?>
                                            <dt><?= h($d['label']) ?></dt>
                                            <dd><?= h($val) ?></dd>
                                        <?php endforeach; ?>
                                    </dl>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$responses): ?>
                            <tr><td colspan="6" class="text-muted text-center py-4">
                                No feedback submitted yet. Share <code>/pages/feedback.php</code> with participants.
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= render_pagination($page, $totalPages, $baseQs() . '&') ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
