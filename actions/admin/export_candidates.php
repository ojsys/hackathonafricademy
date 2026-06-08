<?php
/**
 * Export Candidates to CSV
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

$statusFilter = $_GET['status'] ?? 'all';
$candidates = get_candidates_for_review($statusFilter);

$filename = 'hackathon-candidates-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// CSV header
fputcsv($output, [
    'Name', 'Email', 'Country', 'City', 'Education', 'Experience',
    'Status', 'Courses Completed', 'Quiz Average', 'Total Score',
    'Qualifying Score', 'Qualifying Result', 'Qualifying Attempts',
    'GitHub', 'LinkedIn', 'Portfolio', 'Joined', 'Admin Notes'
]);

// CSV rows
foreach ($candidates as $c) {
    // Best (highest) qualifying attempt, read live so it reflects deletions.
    $qual = get_best_qualifying_attempt((int)$c['id']);
    $qualCountStmt = db()->prepare('SELECT COUNT(*) FROM qualifying_attempts WHERE user_id = ? AND completed_at IS NOT NULL');
    $qualCountStmt->execute([$c['id']]);
    $qualCount = (int)$qualCountStmt->fetchColumn();

    fputcsv($output, [
        $c['name'] ?? '',
        $c['email'] ?? '',
        $c['country'] ?? '',
        $c['city'] ?? '',
        $c['education_level'] ?? '',
        $c['years_experience'] ?? '',
        $c['eligibility_status'] ?? 'pending',
        $c['courses_completed'] ?? 0,
        round($c['avg_quiz_score'] ?? 0) . '%',
        round($c['total_score'] ?? 0) . '%',
        $qual ? round($qual['percentage']) . '%' : '',
        $qual ? ($qual['passed'] ? 'Passed' : 'Failed') : 'Not attempted',
        $qualCount,
        $c['github_url'] ?? '',
        $c['linkedin_url'] ?? '',
        $c['portfolio_url'] ?? '',
        $c['created_at'] ?? '',
        $c['admin_notes'] ?? ''
    ]);
}

fclose($output);
exit;
