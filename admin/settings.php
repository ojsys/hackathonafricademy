<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$settings = get_site_settings();
$logoPath = $settings['logo_path'] ?? '/public/img/logo.png';
$faviconPath = $settings['favicon_path'] ?? '/public/img/favicon.png';
$siteName = $settings['site_name'] ?? 'HackathonAfrica LMS';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-content">
        <h1 class="admin-page-title" data-testid="settings-title">Site Settings</h1>

        <?php render_flash(); ?>

        <!-- Site Name -->
        <div class="settings-section" data-testid="settings-site-name">
            <h3><i class="bi bi-type me-2"></i>Site Name</h3>
            <form action="/actions/admin/update_settings.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="setting_type" value="site_name">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Display Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= h($siteName) ?>" required data-testid="input-site-name">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100" data-testid="save-site-name">
                            <i class="bi bi-check2 me-1"></i> Save Name
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logo Upload -->
        <div class="settings-section" data-testid="settings-logo">
            <h3><i class="bi bi-image me-2"></i>Site Logo</h3>
            
            <div class="current-asset-preview" data-testid="current-logo-preview">
                <img src="<?= h($logoPath) ?>" alt="Current Logo">
                <div>
                    <p class="mb-0 fw-600" style="color: var(--text-primary);">Current Logo</p>
                    <p class="mb-0 small" style="color: var(--text-muted);"><?= h($logoPath) ?></p>
                </div>
            </div>

            <form action="/actions/admin/update_settings.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="setting_type" value="logo">
                
                <div class="file-upload-zone mb-3" data-testid="logo-upload-zone" id="logoUploadZone">
                    <input type="file" name="logo_file" accept="image/png,image/jpeg,image/svg+xml,image/webp" id="logoFileInput" data-testid="input-logo-file">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Click to upload a new logo</p>
                    <p class="small" style="color: var(--text-muted);">PNG, JPG, SVG, or WebP. Recommended: 200x60px or similar aspect ratio</p>
                    <p class="file-name" id="logoFileName"></p>
                </div>

                <button type="submit" class="btn btn-primary" data-testid="save-logo">
                    <i class="bi bi-upload me-1"></i> Upload Logo
                </button>
            </form>
        </div>

        <!-- Favicon Upload -->
        <div class="settings-section" data-testid="settings-favicon">
            <h3><i class="bi bi-app me-2"></i>Favicon</h3>
            
            <div class="current-asset-preview" data-testid="current-favicon-preview">
                <img src="<?= h($faviconPath) ?>" alt="Current Favicon" class="favicon-preview">
                <div>
                    <p class="mb-0 fw-600" style="color: var(--text-primary);">Current Favicon</p>
                    <p class="mb-0 small" style="color: var(--text-muted);"><?= h($faviconPath) ?></p>
                </div>
            </div>

            <form action="/actions/admin/update_settings.php" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="setting_type" value="favicon">
                
                <div class="file-upload-zone mb-3" data-testid="favicon-upload-zone" id="faviconUploadZone">
                    <input type="file" name="favicon_file" accept="image/png,image/jpeg,image/svg+xml,image/x-icon,image/webp" id="faviconFileInput" data-testid="input-favicon-file">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Click to upload a new favicon</p>
                    <p class="small" style="color: var(--text-muted);">PNG, ICO, or SVG. Recommended: 32x32px or 192x192px</p>
                    <p class="file-name" id="faviconFileName"></p>
                </div>

                <button type="submit" class="btn btn-primary" data-testid="save-favicon">
                    <i class="bi bi-upload me-1"></i> Upload Favicon
                </button>
            </form>
        </div>

        <!-- Theme Customizer -->
        <div class="settings-section" data-testid="settings-theme">
            <h3><i class="bi bi-palette me-2"></i>Theme Colors</h3>
            <p class="small" style="color: var(--text-muted);">Customize the accent color of the LMS. Changes apply across buttons, links, and highlights.</p>
            
            <form action="/actions/admin/update_settings.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="setting_type" value="theme">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Primary / Accent Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="primary_color" value="<?= h($settings['primary_color'] ?? '#F8B526') ?>" class="form-control form-control-color" style="width: 60px; height: 42px;" data-testid="input-primary-color">
                            <input type="text" name="primary_color_hex" value="<?= h($settings['primary_color'] ?? '#F8B526') ?>" class="form-control" style="max-width: 120px; font-family: var(--font-mono);" data-testid="input-primary-hex" id="primaryHex">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preview</label>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="btn btn-sm" id="themePreviewBtn" style="background: <?= h($settings['primary_color'] ?? '#F8B526') ?>; color: #0D1117; font-weight: 700;">Sample Button</span>
                            <span class="badge" id="themePreviewBadge" style="background: <?= h($settings['primary_color'] ?? '#F8B526') ?>20; color: <?= h($settings['primary_color'] ?? '#F8B526') ?>; border: 1px solid <?= h($settings['primary_color'] ?? '#F8B526') ?>50;">Badge</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap mb-3">
                    <small class="text-muted me-2">Presets:</small>
                    <button type="button" class="btn btn-sm" style="background: #F8B526; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#F8B526')" title="Gold (Default)"></button>
                    <button type="button" class="btn btn-sm" style="background: #00FF66; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#00FF66')" title="Volt Green"></button>
                    <button type="button" class="btn btn-sm" style="background: #3B82F6; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#3B82F6')" title="Blue"></button>
                    <button type="button" class="btn btn-sm" style="background: #8B5CF6; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#8B5CF6')" title="Purple"></button>
                    <button type="button" class="btn btn-sm" style="background: #EF4444; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#EF4444')" title="Red"></button>
                    <button type="button" class="btn btn-sm" style="background: #06B6D4; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#06B6D4')" title="Cyan"></button>
                    <button type="button" class="btn btn-sm" style="background: #F97316; width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent;" onclick="setColor('#F97316')" title="Orange"></button>
                </div>

                <button type="submit" class="btn btn-primary" data-testid="save-theme">
                    <i class="bi bi-check2 me-1"></i> Save Theme
                </button>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="settings-section" style="border-color: rgba(255, 68, 68, 0.3);">
            <h3 style="color: var(--danger);"><i class="bi bi-exclamation-triangle me-2"></i>Reset to Defaults</h3>
            <p class="small" style="color: var(--text-muted);">This will reset the logo, favicon, and theme back to the default AfricaPlan Foundation branding.</p>
            <form action="/actions/admin/update_settings.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="setting_type" value="reset_branding">
                <button type="submit" class="btn btn-outline-secondary" data-testid="reset-branding" onclick="return confirm('Are you sure you want to reset branding to defaults?')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Branding
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// File upload preview
function setupUploadZone(inputId, nameId) {
    const input = document.getElementById(inputId);
    const nameEl = document.getElementById(nameId);
    if (!input || !nameEl) return;
    
    input.addEventListener('change', function() {
        if (this.files.length > 0) {
            nameEl.textContent = this.files[0].name;
            nameEl.style.display = 'block';
        } else {
            nameEl.style.display = 'none';
        }
    });
}

setupUploadZone('logoFileInput', 'logoFileName');
setupUploadZone('faviconFileInput', 'faviconFileName');

// Theme color picker sync
const colorInput = document.querySelector('[name="primary_color"]');
const hexInput = document.getElementById('primaryHex');
const previewBtn = document.getElementById('themePreviewBtn');
const previewBadge = document.getElementById('themePreviewBadge');

function setColor(color) {
    if (colorInput) colorInput.value = color;
    if (hexInput) hexInput.value = color;
    document.querySelector('[name="primary_color_hex"]').value = color;
    updatePreview(color);
}

function updatePreview(color) {
    if (previewBtn) previewBtn.style.background = color;
    if (previewBadge) {
        previewBadge.style.background = color + '20';
        previewBadge.style.color = color;
        previewBadge.style.borderColor = color + '50';
    }
}

if (colorInput) {
    colorInput.addEventListener('input', function() {
        hexInput.value = this.value;
        document.querySelector('[name="primary_color_hex"]').value = this.value;
        updatePreview(this.value);
    });
}
if (hexInput) {
    hexInput.addEventListener('input', function() {
        if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
            colorInput.value = this.value;
            document.querySelector('[name="primary_color_hex"]').value = this.value;
            updatePreview(this.value);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
