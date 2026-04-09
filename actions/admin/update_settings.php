<?php
/**
 * Admin Settings Update Handler
 * Handles logo, favicon, and site name updates
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
verify_csrf();

$settingType = $_POST['setting_type'] ?? '';
$uploadDir = __DIR__ . '/../../public/img/uploads/';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

switch ($settingType) {
    case 'site_name':
        $name = trim($_POST['site_name'] ?? '');
        if (empty($name)) {
            set_flash('error', 'Site name cannot be empty.');
            break;
        }
        set_setting('site_name', $name);
        set_flash('success', 'Site name updated successfully.');
        break;

    case 'logo':
        if (!isset($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Please select a logo file to upload.');
            break;
        }
        
        $file = $_FILES['logo_file'];
        $result = process_upload($file, 'logo', $uploadDir);
        
        if ($result['success']) {
            set_setting('logo_path', $result['path']);
            set_flash('success', 'Logo updated successfully.');
        } else {
            set_flash('error', $result['error']);
        }
        break;

    case 'favicon':
        if (!isset($_FILES['favicon_file']) || $_FILES['favicon_file']['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Please select a favicon file to upload.');
            break;
        }
        
        $file = $_FILES['favicon_file'];
        $result = process_upload($file, 'favicon', $uploadDir);
        
        if ($result['success']) {
            set_setting('favicon_path', $result['path']);
            set_flash('success', 'Favicon updated successfully.');
        } else {
            set_flash('error', $result['error']);
        }
        break;

    case 'reset_branding':
        set_setting('logo_path', '/public/img/logo.png');
        set_setting('favicon_path', '/public/img/favicon.png');
        set_setting('site_name', 'HackathonAfrica LMS');
        set_setting('primary_color', '#F8B526');
        set_flash('success', 'Branding reset to defaults.');
        break;

    case 'theme':
        $color = trim($_POST['primary_color_hex'] ?? $_POST['primary_color'] ?? '#F8B526');
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            set_setting('primary_color', $color);
            set_flash('success', 'Theme color updated to ' . $color);
        } else {
            set_flash('error', 'Invalid color format. Use hex like #F8B526');
        }
        break;

    case 'email':
        $fields = ['smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_email','smtp_from_name','smtp_encryption'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) set_setting($f, trim($_POST[$f]));
        }
        set_flash('success', 'Email settings saved.');
        break;

    case 'pipeline':
        $deadline = trim($_POST['completion_deadline'] ?? '');
        set_setting('completion_deadline', $deadline);
        $limit = max(1, (int)($_POST['shortlist_limit'] ?? 100));
        set_setting('shortlist_limit', (string)$limit);
        set_flash('success', 'Pipeline settings saved.');
        break;

    default:
        set_flash('error', 'Invalid setting type.');
        break;
}

header('Location: /admin/settings.php');
exit;

/**
 * Process a file upload and return the web-accessible path
 */
function process_upload(array $file, string $prefix, string $uploadDir): array {
    // Validate file type
    $allowedTypes = [
        'image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 
        'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: PNG, JPG, SVG, WebP, ICO'];
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'error' => 'File too large. Maximum size is 2MB.'];
    }
    
    // Generate safe filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeExt = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext));
    if (empty($safeExt)) $safeExt = 'png';
    
    $filename = $prefix . '_' . time() . '.' . $safeExt;
    $destPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return [
            'success' => true, 
            'path' => '/public/img/uploads/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to save uploaded file.'];
}
