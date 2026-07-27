<?php
/**
 * Participant Feedback — public form (Feeding / Accommodation / Training Delivery).
 *
 * Open to everyone: participants do not need an account, and any submission can
 * be made anonymously. All three forms are generated from includes/feedback.php.
 */
$pageTitle = 'Participant Feedback';
require_once __DIR__ . '/../includes/feedback.php';
start_session();

$forms   = feedback_forms();
$user    = current_user();
$isOpen  = feedback_is_open();

$activeTab = $_GET['tab'] ?? '';
if (!isset($forms[$activeTab])) $activeTab = 'feeding';

$submitted = $_GET['submitted'] ?? '';
if (!isset($forms[$submitted])) $submitted = '';
if ($submitted) $activeTab = $submitted;

// Validation errors / previous input handed back by actions/submit_feedback.php
$errors = $_SESSION['feedback_errors'] ?? [];
$old    = $_SESSION['feedback_old'] ?? [];
unset($_SESSION['feedback_errors'], $_SESSION['feedback_old']);
if (isset($forms[$old['category'] ?? ''])) $activeTab = $old['category'];

// True when this panel is being re-rendered with the participant's previous input.
$isRetry = fn(string $category): bool => ($old['category'] ?? '') === $category;

$oldFor = function (string $category, string $key, $default = '') use ($isRetry, $old) {
    if (!$isRetry($category)) return $default;
    return $old['q'][$key] ?? $default;
};

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Participant feedback forms ─────────────────────────── */
.fb-hero { background: var(--surface); border-bottom: 1px solid var(--border); padding: 2rem 0 0; }
.fb-hero h1 { font-size: 1.6rem; margin-bottom: .35rem; }

.fb-tabs { display: flex; gap: 0; overflow-x: auto; margin-top: 1.25rem; }
.fb-tab {
    padding: .8rem 1.4rem; font-size: .9rem; font-weight: 500; white-space: nowrap;
    color: var(--text-muted); text-decoration: none;
    border-bottom: 3px solid transparent; transition: color .15s, border-color .15s;
}
.fb-tab:hover { color: var(--text-primary); }
.fb-tab.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 600; }

.fb-panel { display: none; max-width: 780px; margin: 2rem auto 4rem; }
.fb-panel.active { display: block; }

.fb-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--card-shadow); }
.fb-card-header { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 1.6rem 1.75rem; position: relative; overflow: hidden; }
.fb-card-header::after { content: ''; position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,.12); }
.fb-card-header .fb-icon { font-size: 2rem; line-height: 1; margin-bottom: .5rem; }
.fb-card-header h2 { color: var(--text-on-primary); font-size: 1.3rem; margin-bottom: .2rem; }
.fb-card-header p { color: var(--text-on-primary); opacity: .85; font-size: .9rem; margin: 0; }
.fb-card-body { padding: 1.75rem; }

.fb-field { margin-bottom: 1.5rem; }
.fb-field > label.fb-label {
    display: block; font-size: .78rem; font-weight: 600; letter-spacing: .02em;
    text-transform: uppercase; color: var(--text-primary); margin-bottom: .5rem;
}
.fb-field .req { color: var(--primary); margin-left: 2px; }

.fb-section { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    color: var(--primary); margin: 2rem 0 1.1rem; padding-bottom: .5rem; border-bottom: 2px solid var(--border); }

.fb-rating { display: flex; gap: .5rem; flex-wrap: wrap; }
.fb-rating input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.fb-rating label {
    display: flex; align-items: center; justify-content: center;
    width: 46px; height: 46px; border: 1.5px solid var(--border); border-radius: 8px;
    font-size: .95rem; font-weight: 600; color: var(--text-secondary); cursor: pointer; transition: all .15s;
}
.fb-rating label:hover { border-color: var(--primary); color: var(--primary); }
.fb-rating input[type="radio"]:focus-visible + label { outline: 2px solid var(--primary); outline-offset: 2px; }
.fb-rating input[type="radio"]:checked + label { background: var(--primary); border-color: var(--primary); color: var(--text-on-primary); }
.fb-scale { display: flex; justify-content: space-between; margin-top: .4rem; font-size: .72rem; color: var(--text-muted); max-width: 300px; }

.fb-emoji { display: flex; gap: .6rem; flex-wrap: wrap; }
.fb-emoji input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.fb-emoji label {
    display: flex; flex-direction: column; align-items: center; gap: .25rem;
    padding: .6rem .85rem; min-width: 72px; text-align: center;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-size: .7rem; color: var(--text-secondary); cursor: pointer; transition: all .15s;
}
.fb-emoji label .fb-e { font-size: 1.5rem; line-height: 1.2; }
.fb-emoji label:hover { border-color: var(--primary); }
.fb-emoji input[type="radio"]:focus-visible + label { outline: 2px solid var(--primary); outline-offset: 2px; }
.fb-emoji input[type="radio"]:checked + label { border-color: var(--primary); background: var(--primary-glow); color: var(--primary); font-weight: 600; }

.fb-checks { display: flex; flex-direction: column; gap: .6rem; }
.fb-check { display: flex; align-items: center; gap: .6rem; font-size: .9rem; color: var(--text-secondary); cursor: pointer; }
.fb-check input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0; }

.fb-field input[type="text"], .fb-field input[type="date"], .fb-field textarea {
    width: 100%; padding: .65rem .85rem; font-size: .92rem;
    background: var(--bg); color: var(--text-primary);
    border: 1.5px solid var(--border); border-radius: 8px; outline: none; transition: border-color .15s, box-shadow .15s;
}
.fb-field input:focus, .fb-field textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }
.fb-field textarea { resize: vertical; min-height: 92px; }
.fb-field input.is-invalid, .fb-field textarea.is-invalid { border-color: var(--danger); }
.fb-error { color: var(--danger); font-size: .78rem; margin-top: .35rem; }

.fb-identity { background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.1rem 1.1rem .3rem; margin-bottom: .5rem; }
.fb-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }

.fb-submit-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .9rem;
    margin-top: 2rem; padding-top: 1.3rem; border-top: 1px solid var(--border); }
.fb-note { font-size: .78rem; color: var(--text-muted); }

.fb-success { border: 1px solid var(--success); border-radius: var(--radius); background: rgba(46,160,67,.1);
    padding: 1.6rem; text-align: center; margin-bottom: 1.25rem; }
.fb-success .fb-check-icon { font-size: 2.2rem; }
.fb-success h3 { color: var(--success); font-size: 1.1rem; margin: .5rem 0 .25rem; }
.fb-success p { color: var(--text-secondary); font-size: .9rem; margin: 0; }

.fb-hp { position: absolute; left: -9999px; opacity: 0; height: 0; overflow: hidden; }

@media (max-width: 600px) {
    .fb-two-col { grid-template-columns: 1fr; }
    .fb-card-body { padding: 1.25rem; }
    .fb-tab { padding: .7rem .9rem; font-size: .85rem; }
}
</style>

<section class="fb-hero">
    <div class="container">
        <span class="overline d-block mb-2">HACKATHONAFRICA 3.0</span>
        <h1>Participant Feedback</h1>
        <p class="text-muted mb-0" style="max-width:640px">
            Tell us how the camp is going. Your responses go straight to the organising team and shape what we
            change next — you can submit completely anonymously.
        </p>
        <div class="fb-tabs">
            <?php foreach ($forms as $cat => $form): ?>
                <a href="?tab=<?= h($cat) ?>" class="fb-tab <?= $activeTab === $cat ? 'active' : '' ?>"
                   data-fb-tab="<?= h($cat) ?>" data-testid="feedback-tab-<?= h($cat) ?>">
                    <?= $form['icon'] ?> <?= h($form['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="container">
<div style="max-width:780px;margin:1.5rem auto 0"><?php render_flash(); ?></div>
<?php if (!$isOpen): ?>
    <div class="fb-panel active">
        <div class="alert alert-warning mt-4">
            <i class="bi bi-info-circle me-2"></i>
            Feedback collection is currently closed. Please check back later or speak to a facilitator.
        </div>
    </div>
<?php else: ?>

<?php foreach ($forms as $cat => $form): ?>
    <div class="fb-panel <?= $activeTab === $cat ? 'active' : '' ?>" id="fb-panel-<?= h($cat) ?>">

        <?php if ($submitted === $cat): ?>
            <div class="fb-success" data-testid="feedback-success-<?= h($cat) ?>">
                <div class="fb-check-icon">✅</div>
                <h3>Thank you for your feedback!</h3>
                <p><?= h($form['success']) ?></p>
                <a href="?tab=<?= h($cat) ?>" class="btn btn-outline-primary btn-sm mt-3">Submit another response</a>
            </div>
        <?php endif; ?>

        <div class="fb-card">
            <div class="fb-card-header">
                <div class="fb-icon"><?= $form['icon'] ?></div>
                <h2><?= h($form['title']) ?></h2>
                <p><?= h($form['intro']) ?></p>
            </div>
            <div class="fb-card-body">
                <?php if ($errors && $isRetry($cat)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Please complete the highlighted questions below.
                    </div>
                <?php endif; ?>

                <form method="POST" action="/actions/submit_feedback.php" data-testid="feedback-form-<?= h($cat) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="category" value="<?= h($cat) ?>">
                    <div class="fb-hp" aria-hidden="true">
                        <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <?php
                    $isAnon  = $isRetry($cat) ? !empty($old['anonymous'])          : ($user === null);
                    $nameVal = $isRetry($cat) ? ($old['name'] ?? '')                : ($user['name'] ?? '');
                    $dateVal = $isRetry($cat) ? ($old['feedback_date'] ?? date('Y-m-d')) : date('Y-m-d');
                    ?>
                    <div class="fb-identity">
                        <div class="fb-field">
                            <label class="fb-check">
                                <input type="checkbox" name="anonymous" value="1" class="fb-anon-toggle" <?= $isAnon ? 'checked' : '' ?>>
                                <span>Submit anonymously — don't record my name</span>
                            </label>
                        </div>
                        <div class="fb-two-col">
                            <div class="fb-field fb-name-field">
                                <label class="fb-label">Full Name <span class="req">*</span></label>
                                <input type="text" name="name" value="<?= h($nameVal) ?>" placeholder="Your name"
                                       class="<?= isset($errors['name']) && $isRetry($cat) ? 'is-invalid' : '' ?>">
                                <?php if (isset($errors['name']) && $isRetry($cat)): ?>
                                    <div class="fb-error"><?= h($errors['name']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="fb-field">
                                <label class="fb-label">Date <span class="req">*</span></label>
                                <input type="date" name="feedback_date" value="<?= h($dateVal) ?>" required>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($form['sections'] as $sectionLabel => $fields): ?>
                        <?php if ($sectionLabel !== ''): ?>
                            <div class="fb-section"><?= h($sectionLabel) ?></div>
                        <?php endif; ?>

                        <?php foreach ($fields as $key => $def):
                            $hasError = isset($errors[$key]) && $isRetry($cat);
                            $prev = $oldFor($cat, $key);
                            $id = $cat . '_' . $key;
                        ?>
                        <div class="fb-field" data-testid="fb-field-<?= h($id) ?>">
                            <label class="fb-label"><?= h($def['label']) ?><?= !empty($def['required']) ? ' <span class="req">*</span>' : '' ?></label>

                            <?php if ($def['type'] === 'rating'): ?>
                                <div class="fb-rating">
                                    <?php foreach ([1, 2, 3, 4, 5] as $n): ?>
                                        <input type="radio" name="q[<?= h($key) ?>]" id="<?= h($id) ?>_<?= $n ?>" value="<?= $n ?>"
                                               <?= (string)$prev === (string)$n ? 'checked' : '' ?>>
                                        <label for="<?= h($id) ?>_<?= $n ?>"><?= $n ?></label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="fb-scale"><span><?= h($def['low']) ?></span><span><?= h($def['high']) ?></span></div>

                            <?php elseif ($def['type'] === 'emoji'): ?>
                                <div class="fb-emoji">
                                    <?php foreach ($def['options'] as $value => [$emoji, $optLabel]): ?>
                                        <input type="radio" name="q[<?= h($key) ?>]" id="<?= h($id . '_' . $value) ?>" value="<?= h($value) ?>"
                                               <?= (string)$prev === (string)$value ? 'checked' : '' ?>>
                                        <label for="<?= h($id . '_' . $value) ?>">
                                            <span class="fb-e"><?= $emoji ?></span><?= h($optLabel) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($def['type'] === 'checkbox'):
                                $picked = $prev !== '' ? explode('; ', (string)$prev) : [];
                            ?>
                                <div class="fb-checks">
                                    <?php foreach ($def['options'] as $value => $optLabel): ?>
                                        <label class="fb-check">
                                            <input type="checkbox" name="q[<?= h($key) ?>][]" value="<?= h($value) ?>"
                                                   <?= in_array((string)$value, $picked, true) ? 'checked' : '' ?>>
                                            <span><?= h($optLabel) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($def['type'] === 'textarea'): ?>
                                <textarea name="q[<?= h($key) ?>]" placeholder="<?= h($def['placeholder'] ?? '') ?>"
                                          class="<?= $hasError ? 'is-invalid' : '' ?>"><?= h((string)$prev) ?></textarea>

                            <?php else: ?>
                                <input type="text" name="q[<?= h($key) ?>]" value="<?= h((string)$prev) ?>"
                                       placeholder="<?= h($def['placeholder'] ?? '') ?>"
                                       class="<?= $hasError ? 'is-invalid' : '' ?>">
                            <?php endif; ?>

                            <?php if ($hasError): ?>
                                <div class="fb-error"><?= h($errors[$key]) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    <div class="fb-submit-row">
                        <span class="fb-note"><i class="bi bi-shield-lock me-1"></i>Your feedback is used only to improve the program.</span>
                        <button type="submit" class="btn btn-primary" data-testid="feedback-submit-<?= h($cat) ?>">
                            Submit Feedback <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<script>
(function () {
    // Tab switching without a page reload (links still work if JS is off).
    document.querySelectorAll('[data-fb-tab]').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            var cat = tab.getAttribute('data-fb-tab');
            var panel = document.getElementById('fb-panel-' + cat);
            if (!panel) return;
            e.preventDefault();
            document.querySelectorAll('[data-fb-tab]').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.fb-panel').forEach(function (p) { p.classList.remove('active'); });
            tab.classList.add('active');
            panel.classList.add('active');
            history.replaceState(null, '', '?tab=' + cat);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // "Submit anonymously" hides the name field; the server enforces this too.
    document.querySelectorAll('.fb-anon-toggle').forEach(function (toggle) {
        var form = toggle.closest('form');
        var wrap = form.querySelector('.fb-name-field');
        var input = wrap.querySelector('input');
        function sync() {
            wrap.style.display = toggle.checked ? 'none' : '';
            input.required = !toggle.checked;
            if (toggle.checked) input.value = '';
        }
        toggle.addEventListener('change', sync);
        sync();
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
