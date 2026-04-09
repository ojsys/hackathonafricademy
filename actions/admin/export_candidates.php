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
    'GitHub', 'LinkedIn', 'Portfolio', 'Joined', 'Admin Notes'
]);

// CSV rows
foreach ($candidates as $c) {
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
        $c['github_url'] ?? '',
        $c['linkedin_url'] ?? '',
        $c['portfolio_url'] ?? '',
        $c['created_at'] ?? '',
        $c['admin_notes'] ?? ''
    ]);
}

fclose($output);
exit;
