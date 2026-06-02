<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: [];

start_session();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'] ?? '')) {
    http_response_code(403); echo json_encode(['ok' => false]); exit;
}

$user      = current_user();
$sessionId = (int)($data['session_id'] ?? 0);
$type      = substr(preg_replace('/[^a-z_]/', '', strtolower($data['event_type'] ?? '')), 0, 40);
$detail    = substr((string)($data['detail'] ?? ''), 0, 300);

if ($type === '') { echo json_encode(['ok' => false]); exit; }

$session = get_interview_session($sessionId);
if (!$session || (int)$session['user_id'] !== (int)$user['id'] || $session['status'] !== 'in_progress') {
    http_response_code(403); echo json_encode(['ok' => false]); exit;
}

db()->prepare('INSERT INTO interview_events (session_id, user_id, event_type, detail) VALUES (?, ?, ?, ?)')
   ->execute([$sessionId, $user['id'], $type, $detail]);

echo json_encode(['ok' => true]);
