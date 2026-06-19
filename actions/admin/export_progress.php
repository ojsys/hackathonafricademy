<?php
/**
 * CSV export of the Progress Reports learner table.
 * Honours the same search + sort as admin/progress.php (no pagination).
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$sort   = $_GET['sort'] ?? 'active';

$where  = "u.role='student'";
$params = [];
if ($search !== '') {
    $where  .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderMap = [
    'active'  => 'last_active DESC',
    'lessons' => 'lessons_done DESC',
    'score'   => 'avg_quiz_score DESC',
    'newest'  => 'u.created_at DESC',
    'name'    => 'u.name ASC',
];
$orderBy = $orderMap[$sort] ?? $orderMap['active'];

$totalPubLessons = (int)db()->query("SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id JOIN courses c ON m.course_id=c.id WHERE c.status='published'")->fetchColumn();

$stmt = db()->prepare("
    SELECT u.id, u.name, u.email, u.country, u.created_at,
        (SELECT COUNT(*) FROM user_enrollments WHERE user_id=u.id) AS enrollments,
        (SELECT COUNT(*) FROM user_lesson_progress p
            JOIN lessons l ON l.id=p.lesson_id JOIN modules m ON m.id=l.module_id
            JOIN courses c ON c.id=m.course_id AND c.status='published'
            WHERE p.user_id=u.id) AS lessons_done,
        (SELECT COUNT(*) FROM quiz_attempts WHERE user_id=u.id) AS quiz_attempts,
        (SELECT COUNT(*) FROM quiz_attempts WHERE user_id=u.id AND passed=1) AS quizzes_passed,
        (SELECT ROUND(AVG(score),1) FROM quiz_attempts WHERE user_id=u.id) AS avg_quiz_score,
        (SELECT COUNT(DISTINCT exam_id) FROM final_exam_attempts WHERE user_id=u.id AND passed=1) AS exams_passed,
        MAX(
            COALESCE(u.last_activity,'1970-01-01'),
            COALESCE((SELECT MAX(completed_at) FROM user_lesson_progress WHERE user_id=u.id),'1970-01-01'),
            COALESCE((SELECT MAX(attempted_at) FROM quiz_attempts WHERE user_id=u.id),'1970-01-01')
        ) AS last_active
    FROM users u
    WHERE $where
    ORDER BY $orderBy
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Filename reflects active filters
$parts = ['learner_progress'];
if ($search) $parts[] = preg_replace('/[^a-z0-9]+/i', '_', strtolower($search));
if ($sort && $sort !== 'active') $parts[] = $sort;
$parts[] = date('Y-m-d');
$filename = implode('_', $parts) . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

fputcsv($out, [
    'ID', 'Full Name', 'Email', 'Country',
    'Courses Enrolled', 'Lessons Completed', 'Total Published Lessons', 'Overall Completion (%)',
    'Quiz Attempts', 'Quizzes Passed', 'Avg Quiz Score (%)', 'Final Exams Passed',
    'Last Active', 'Date Joined',
]);

foreach ($rows as $r) {
    $overall = $totalPubLessons > 0 ? round($r['lessons_done'] / $totalPubLessons * 100) : 0;
    $last = (!$r['last_active'] || $r['last_active'] < '2000-01-01') ? 'never' : $r['last_active'];
    fputcsv($out, [
        $r['id'],
        $r['name'],
        $r['email'],
        $r['country'],
        (int)$r['enrollments'],
        (int)$r['lessons_done'],
        $totalPubLessons,
        $overall,
        (int)$r['quiz_attempts'],
        (int)$r['quizzes_passed'],
        $r['avg_quiz_score'] !== null ? $r['avg_quiz_score'] : '',
        (int)$r['exams_passed'],
        $last,
        $r['created_at'],
    ]);
}
fclose($out);
