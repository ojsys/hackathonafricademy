<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

// ── Rebuild the same filters from users.php ───────────────────────────────────
$search     = trim($_GET['q'] ?? '');
$role       = $_GET['role'] ?? '';
$statusFilt = $_GET['status'] ?? '';
$eligible   = $_GET['eligible'] ?? '';
$experience = $_GET['experience'] ?? '';
$country    = trim($_GET['country'] ?? '');
$minLessons = $_GET['min_lessons'] ?? '';
$sort       = $_GET['sort'] ?? 'newest';

$params = [];
$where  = [];

if ($search) {
    $where[]  = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($role && in_array($role, ['student', 'admin'])) {
    $where[]  = 'u.role = ?';
    $params[] = $role;
}
if ($statusFilt === 'active') {
    $where[] = 'u.is_active = 1';
} elseif ($statusFilt === 'inactive') {
    $where[] = 'u.is_active = 0';
}
if ($eligible === 'yes') {
    $where[] = 'cr.eligibility_status = "eligible"';
} elseif ($eligible === 'no') {
    $where[] = '(cr.eligibility_status IS NULL OR cr.eligibility_status != "eligible")';
}
if ($experience && in_array($experience, ['none','lt1','1-2','3-5','5+'])) {
    $where[]  = 'u.years_experience = ?';
    $params[] = $experience;
}
if ($country) {
    $where[]  = 'u.country LIKE ?';
    $params[] = "%$country%";
}
if ($minLessons !== '' && is_numeric($minLessons)) {
    $where[]  = '(SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) >= ?';
    $params[] = (int) $minLessons;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sortMap = [
    'newest'       => 'u.created_at DESC',
    'oldest'       => 'u.created_at ASC',
    'lessons_desc' => 'lessons_done DESC',
    'score_desc'   => 'COALESCE(cr.composite_score, 0) DESC',
    'quiz_desc'    => 'COALESCE(cr.avg_quiz_score, 0) DESC',
    'name_asc'     => 'u.name ASC',
];
$orderSql = $sortMap[$sort] ?? 'u.created_at DESC';

// Fetch ALL matching users (no pagination limit)
$stmt = db()->prepare("
    SELECT
        u.id,
        u.name,
        u.email,
        u.phone,
        u.gender,
        u.city,
        u.country,
        u.education_level,
        u.years_experience,
        u.how_heard,
        u.github_url,
        u.linkedin_url,
        u.bio,
        u.role,
        u.is_active,
        u.created_at,
        (SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) AS lessons_done,
        (SELECT COUNT(*) FROM user_enrollments      WHERE user_id = u.id) AS enrollments,
        COALESCE(cr.courses_completed, 0)   AS courses_completed,
        COALESCE(cr.avg_quiz_score, 0)      AS avg_quiz_score,
        COALESCE(cr.avg_exam_score, 0)      AS avg_exam_score,
        COALESCE(cr.composite_score, 0)     AS composite_score,
        COALESCE(cr.qualifying_score, 0)    AS qualifying_score,
        cr.qualifying_passed,
        cr.eligibility_status,
        cr.speed_bonus,
        cr.attempt_penalty
    FROM users u
    LEFT JOIN candidate_reviews cr ON cr.user_id = u.id
    $whereSql
    ORDER BY $orderSql
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// ── Filename reflects active filters ─────────────────────────────────────────
$parts = ['hackathon_users'];
if ($role)       $parts[] = $role;
if ($eligible)   $parts[] = 'eligible_' . $eligible;
if ($country)    $parts[] = strtolower(str_replace(' ', '_', $country));
$parts[] = date('Y-m-d');
$filename = implode('_', $parts) . '.csv';

// ── CSV output headers ────────────────────────────────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');

// UTF-8 BOM so Excel opens it correctly
fwrite($out, "\xEF\xBB\xBF");

$expLabels = [
    'none' => 'Beginner (none)',
    'lt1'  => '< 1 year',
    '1-2'  => '1–2 years',
    '3-5'  => '3–5 years',
    '5+'   => '5+ years',
];

// ── Header row ────────────────────────────────────────────────────────────────
fputcsv($out, [
    'ID',
    'Full Name',
    'Email',
    'Phone',
    'Gender',
    'City',
    'Country',
    'Education Level',
    'Years Experience',
    'How They Heard',
    'GitHub URL',
    'LinkedIn URL',
    'Bio',
    'Role',
    'Account Status',
    'Lessons Completed',
    'Courses Completed',
    'Quiz Avg Score (%)',
    'Final Exam Avg (%)',
    'Qualifying Exam Score (%)',
    'Qualifying Passed',
    'Composite Score (%)',
    'Speed Bonus',
    'Attempt Penalty',
    'Eligibility Status',
    'Date Joined',
]);

// ── Data rows ─────────────────────────────────────────────────────────────────
foreach ($users as $u) {
    fputcsv($out, [
        $u['id'],
        $u['name'],
        $u['email'],
        $u['phone'] ?? '',
        $u['gender'] ? ucfirst($u['gender']) : '',
        $u['city'] ?? '',
        $u['country'] ?? '',
        $u['education_level'] ?? '',
        $expLabels[$u['years_experience'] ?? ''] ?? ($u['years_experience'] ?? ''),
        $u['how_heard'] ?? '',
        $u['github_url'] ?? '',
        $u['linkedin_url'] ?? '',
        $u['bio'] ?? '',
        $u['role'],
        $u['is_active'] ? 'Active' : 'Inactive',
        (int) $u['lessons_done'],
        (int) $u['courses_completed'],
        round((float) $u['avg_quiz_score'], 1),
        round((float) $u['avg_exam_score'], 1),
        (int) $u['qualifying_score'],
        $u['qualifying_passed'] ? 'Yes' : 'No',
        round((float) $u['composite_score'], 1),
        round((float) $u['speed_bonus'], 2),
        round((float) $u['attempt_penalty'], 2),
        $u['eligibility_status'] ?? 'pending',
        $u['created_at'] ? date('Y-m-d H:i', strtotime($u['created_at'])) : '',
    ]);
}

fclose($out);
exit;
