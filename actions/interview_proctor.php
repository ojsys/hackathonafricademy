<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: [];

start_session();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'] ?? '')) {
    http_response_code(403); echo json_encode(['ok' => false, 'error' => 'invalid_token']); exit;
}

$user      = current_user();
$sessionId = (int)($data['session_id'] ?? 0);
$action    = $data['action'] ?? 'capture';

$session = get_interview_session($sessionId);
if (!$session || (int)$session['user_id'] !== (int)$user['id'] || $session['status'] !== 'in_progress') {
    http_response_code(403); echo json_encode(['ok' => false, 'error' => 'invalid_session']); exit;
}

if ($action === 'camera_granted') {
    db()->prepare('INSERT INTO interview_events (session_id, user_id, event_type, detail) VALUES (?, ?, ?, ?)')
       ->execute([$sessionId, $user['id'], 'camera_granted', 'Camera access granted']);
    echo json_encode(['ok' => true]); exit;
}

if ($action === 'capture') {
    $imageData = $data['image'] ?? '';
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,/', $imageData, $m)) {
        echo json_encode(['ok' => false, 'error' => 'invalid_image']); exit;
    }
    $decoded = base64_decode(substr($imageData, strpos($imageData, ',') + 1), true);
    if ($decoded === false || strlen($decoded) < 100 || strlen($decoded) > 500_000) {
        echo json_encode(['ok' => false, 'error' => 'invalid_image_data']); exit;
    }

    $userDir = __DIR__ . '/../public/img/interview_proctor/' . $user['id'];
    if (!is_dir($userDir)) mkdir($userDir, 0755, true);

    $ext      = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $filename = 'session_' . $sessionId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (file_put_contents($userDir . '/' . $filename, $decoded) === false) {
        echo json_encode(['ok' => false, 'error' => 'write_failed']); exit;
    }

    $rel = 'img/interview_proctor/' . $user['id'] . '/' . $filename;
    db()->prepare('INSERT INTO interview_proctor_images (session_id, user_id, image_path) VALUES (?, ?, ?)')
       ->execute([$sessionId, $user['id'], $rel]);

    echo json_encode(['ok' => true]); exit;
}

echo json_encode(['ok' => false, 'error' => 'unknown_action']);
