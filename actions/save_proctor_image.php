<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// CSRF check
start_session();
$token = $data['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_token']);
    exit;
}

$user      = current_user();
$attemptId = (int)($data['attempt_id'] ?? 0);
$sessionId = (int)($data['session_id'] ?? 0);
$action    = $data['action'] ?? 'capture';

// Verify this attempt belongs to the current user
$stmt = db()->prepare('SELECT * FROM qualifying_attempts WHERE id = ? AND user_id = ? AND completed_at IS NULL');
$stmt->execute([$attemptId, $user['id']]);
$attempt = $stmt->fetch();
if (!$attempt) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'invalid_attempt']);
    exit;
}

// Mark camera as granted
if ($action === 'camera_granted') {
    db()->prepare('UPDATE proctor_sessions SET camera_granted = 1 WHERE id = ? AND user_id = ?')
        ->execute([$sessionId, $user['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

// Save image capture
if ($action === 'capture') {
    $imageData = $data['image'] ?? '';

    // Validate base64 JPEG/PNG data URL
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $imageData, $m)) {
        echo json_encode(['ok' => false, 'error' => 'invalid_image']);
        exit;
    }

    $base64  = substr($imageData, strpos($imageData, ',') + 1);
    $decoded = base64_decode($base64, strict: true);
    if ($decoded === false || strlen($decoded) < 100 || strlen($decoded) > 500_000) {
        echo json_encode(['ok' => false, 'error' => 'invalid_image_data']);
        exit;
    }

    // Build storage path
    $userDir = __DIR__ . '/../public/img/proctor/' . $user['id'];
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }

    $ext      = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $filename = 'attempt_' . $attemptId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fullPath = $userDir . '/' . $filename;

    if (file_put_contents($fullPath, $decoded) === false) {
        echo json_encode(['ok' => false, 'error' => 'write_failed']);
        exit;
    }

    $relativePath = 'img/proctor/' . $user['id'] . '/' . $filename;

    $stmt = db()->prepare('INSERT INTO proctor_images (session_id, user_id, attempt_id, image_path) VALUES (?, ?, ?, ?)');
    $stmt->execute([$sessionId, $user['id'], $attemptId, $relativePath]);

    echo json_encode(['ok' => true, 'path' => $relativePath]);
    exit;
}

echo json_encode(['ok' => false, 'error' => 'unknown_action']);
