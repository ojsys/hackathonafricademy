<?php
/**
 * Sandbox store for the applied "form → database" interview tasks.
 *
 * The candidate's live preview runs same-origin (a blob iframe), so it can call
 * this endpoint with their login cookie. A wrapper injected into the preview
 * adds two headers automatically:
 *   X-Interview-Token    — must equal the session CSRF token (CSRF defence;
 *                          a cross-site page cannot read it or set the header)
 *   X-Interview-Exercise — the current exercise id (scopes the data)
 *
 * GET  ?action=list  -> { ok, entries:[...], count }
 * POST (JSON body)   -> stores the submitted fields, returns { ok, entries, count }
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$user = current_user();

// The candidate must have a live interview session.
$session = get_interview_session_for_user($user['id']);
if (!$session || $session['status'] !== 'in_progress') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'no_active_session']);
    exit;
}
$sessionId = (int)$session['id'];

// Exercise scope comes from the injected header (falls back to a query param).
$headers     = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];
$exerciseId  = (int)($headers['x-interview-exercise'] ?? ($_GET['exercise_id'] ?? ($_POST['exercise_id'] ?? 0)));

// Confirm the exercise is part of this session.
$orderedIds = array_map('intval', json_decode($session['exercise_ids_json'] ?? '[]', true) ?: []);
if (!in_array($exerciseId, $orderedIds, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_exercise']);
    exit;
}

$action = $_GET['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'submit' : 'list');

function sandbox_entries(int $sessionId, int $exerciseId): array {
    $stmt = db()->prepare('SELECT payload_json, created_at FROM interview_sandbox_entries
                           WHERE session_id = ? AND exercise_id = ? ORDER BY id');
    $stmt->execute([$sessionId, $exerciseId]);
    $rows = [];
    foreach ($stmt->fetchAll() as $r) {
        $data = json_decode($r['payload_json'], true) ?: [];
        $data['_at'] = $r['created_at'];
        $rows[] = $data;
    }
    return $rows;
}

if ($action === 'submit') {
    // CSRF: require the injected token to match the session token (header,
    // with a query fallback for hosts where custom headers are unavailable).
    $token = $headers['x-interview-token'] ?? ($_GET['t'] ?? '');
    start_session();
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'invalid_token']);
        exit;
    }

    // Read JSON body (or form-encoded fallback).
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);
    if (!is_array($body)) $body = $_POST;

    // Sanitise: keep a handful of short scalar string fields only.
    $payload = [];
    foreach ($body as $k => $v) {
        if (in_array($k, ['exercise_id', 'csrf_token'], true)) continue;
        if (!is_scalar($v)) continue;
        $key = substr(preg_replace('/[^A-Za-z0-9_]/', '', (string)$k), 0, 40);
        if ($key === '') continue;
        $payload[$key] = substr((string)$v, 0, 500);
        if (count($payload) >= 12) break;
    }
    if (empty($payload)) {
        echo json_encode(['ok' => false, 'error' => 'empty_payload']);
        exit;
    }

    // Cap rows per exercise to keep the scratch store small.
    $cntStmt = db()->prepare('SELECT COUNT(*) FROM interview_sandbox_entries WHERE session_id = ? AND exercise_id = ?');
    $cntStmt->execute([$sessionId, $exerciseId]);
    if ((int)$cntStmt->fetchColumn() < 200) {
        db()->prepare('INSERT INTO interview_sandbox_entries (session_id, user_id, exercise_id, payload_json) VALUES (?, ?, ?, ?)')
            ->execute([$sessionId, $user['id'], $exerciseId, json_encode($payload)]);
    }

    $entries = sandbox_entries($sessionId, $exerciseId);
    echo json_encode(['ok' => true, 'entries' => $entries, 'count' => count($entries)]);
    exit;
}

// Default: list
$entries = sandbox_entries($sessionId, $exerciseId);
echo json_encode(['ok' => true, 'entries' => $entries, 'count' => count($entries)]);
