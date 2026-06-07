<?php
/**
 * Log a final-exam integrity event (proctoring flag).
 *
 * Called from pages/exam_take.php while a student is taking a final exam.
 * Events are stored with attempt_id NULL and later bound to the submitted
 * attempt by actions/submit_exam.php.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: [];

start_session();
if (!hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'] ?? '')) {
    http_response_code(403); echo json_encode(['ok' => false]); exit;
}

$user   = current_user();
$examId = (int)($data['exam_id'] ?? 0);
$type   = substr(preg_replace('/[^a-z_]/', '', strtolower($data['event_type'] ?? '')), 0, 40);
$detail = substr((string)($data['detail'] ?? ''), 0, 300);

if ($type === '' || $examId <= 0) { echo json_encode(['ok' => false]); exit; }

// Make sure the exam actually exists before recording anything against it.
$exists = db()->prepare('SELECT 1 FROM final_exams WHERE id = ?');
$exists->execute([$examId]);
if (!$exists->fetchColumn()) { http_response_code(404); echo json_encode(['ok' => false]); exit; }

db()->prepare('INSERT INTO final_exam_events (user_id, exam_id, attempt_id, event_type, detail) VALUES (?, ?, NULL, ?, ?)')
   ->execute([$user['id'], $examId, $type, $detail]);

echo json_encode(['ok' => true]);
