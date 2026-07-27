<?php
/**
 * Excel export of participant feedback.
 *
 *   ?category=feeding|accommodation|training   one sheet per form, one column per question
 *   ?category=all                              every response in one combined sheet
 *
 * CSV with a UTF-8 BOM so Excel opens it directly with emoji/accents intact.
 */
require_once __DIR__ . '/../../includes/feedback.php';
require_admin();

$category = $_GET['category'] ?? 'all';
if ($category !== 'all' && !feedback_form($category)) {
    http_response_code(400);
    die('Unknown feedback category.');
}

$responses = feedback_get_responses($category);

$filename = 'feedback_' . $category . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

if ($category === 'all') {
    // Long format: one row per answer, so all three forms fit one sheet.
    fputcsv($out, [
        'Response ID', 'Form', 'Submitted At', 'Date of Experience',
        'Anonymous', 'Respondent Name', 'Account Email', 'Question', 'Answer',
    ]);

    foreach ($responses as $r) {
        $form = feedback_form($r['category']);
        if (!$form) continue;
        $fields = feedback_fields($r['category']);
        $meta = [
            $r['id'],
            $form['label'],
            $r['submitted_at'],
            $r['feedback_date'] ?? '',
            ((int)$r['is_anonymous'] === 1) ? 'Yes' : 'No',
            ((int)$r['is_anonymous'] === 1) ? '' : ($r['respondent_name'] ?? $r['user_name'] ?? ''),
            ((int)$r['is_anonymous'] === 1) ? '' : ($r['user_email'] ?? ''),
        ];
        foreach ($fields as $key => $def) {
            $value = feedback_display_answer($def, $r['answers'][$key] ?? '');
            if ($value === '') continue;
            fputcsv($out, array_merge($meta, [$def['label'], $value]));
        }
    }
} else {
    // Wide format: one row per response, one column per question.
    $fields = feedback_fields($category);

    $header = ['Response ID', 'Submitted At', 'Date of Experience', 'Anonymous', 'Respondent Name', 'Account Email'];
    foreach ($fields as $def) $header[] = $def['label'];
    fputcsv($out, $header);

    foreach ($responses as $r) {
        $anon = (int)$r['is_anonymous'] === 1;
        $row = [
            $r['id'],
            $r['submitted_at'],
            $r['feedback_date'] ?? '',
            $anon ? 'Yes' : 'No',
            $anon ? '' : ($r['respondent_name'] ?? $r['user_name'] ?? ''),
            $anon ? '' : ($r['user_email'] ?? ''),
        ];
        foreach ($fields as $key => $def) {
            $row[] = feedback_display_answer($def, $r['answers'][$key] ?? '');
        }
        fputcsv($out, $row);
    }
}

fclose($out);
