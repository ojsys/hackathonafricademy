<?php
/**
 * Final Exam — Additional 20 Questions (v2)
 * Run ONCE after seed_questions.php, then DELETE this file.
 * Access: /seed_questions_v2.php?key=hackathon2026seedv2
 */

define('SEED_PASSWORD', 'hackathon2026seedv2');

if (($_GET['key'] ?? '') !== SEED_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026seedv2 to the URL.</p>');
}

require_once __DIR__ . '/config/database.php';

$exam = db()->query('SELECT * FROM qualifying_exam WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch();
if (!$exam) {
    die('<h3>No active Final Exam found.</h3><p>Run seed_questions.php first or create the exam in the admin panel.</p>');
}
$examId = $exam['id'];

$currentCount = (int)db()->query("SELECT COUNT(*) FROM qualifying_questions WHERE exam_id = $examId")->fetchColumn();
$startOrder   = $currentCount + 1;

// ── 20 Additional Questions ────────────────────────────────────────────────
// Format: [course_tag, question_text, [optA, optB, optC, optD], correct_index (0-3), points]
$questions = [

    // ── HTML (7) ──────────────────────────────────────────────────────────

    ['HTML',
     'What is the semantic difference between `<strong>` and `<b>`?',
     [
         'They are identical — both make text bold',
         '`<strong>` conveys importance semantically; `<b>` is purely visual styling with no meaning',
         '`<b>` is the semantic tag; `<strong>` is visual only',
         '`<strong>` is deprecated in HTML5',
     ], 1, 1],

    ['HTML',
     'What does the `action` attribute on a `<form>` element define?',
     [
         'The HTTP method (GET or POST) used to send data',
         'The CSS class applied when the form is submitted',
         'The URL the form data is sent to when submitted',
         'The validation rules for the form fields',
     ], 2, 1],

    ['HTML',
     'What is the benefit of associating a `<label>` with an input using the `for` attribute?',
     [
         'It applies bold styling to the input',
         'It prevents the input from being submitted',
         'Clicking the label focuses the input, improving accessibility and usability',
         'It makes the field required',
     ], 2, 1],

    ['HTML',
     'What does the `srcset` attribute on an `<img>` tag allow you to do?',
     [
         'Load multiple images simultaneously as a slideshow',
         'Provide different image files for different screen sizes and resolutions',
         'Set a fallback image if the primary source fails to load',
         'Apply CSS filters directly to the image',
     ], 1, 1],

    ['HTML',
     'What is the key rule about the `id` attribute in HTML?',
     [
         '`id` values can be reused across elements for grouping',
         'Each `id` value must be unique within the entire page',
         '`id` is only recognised by JavaScript, not CSS',
         '`id` values are case-insensitive',
     ], 1, 1],

    ['HTML',
     'Which HTML element pair is used to group related form controls and provide them with a visible label?',
     [
         '<form> and <label>',
         '<section> and <h2>',
         '<fieldset> and <legend>',
         '<div> and <span>',
     ], 2, 1],

    ['HTML',
     'What is the correct HTML to create a table header cell that spans across two columns?',
     [
         '<th rowspan="2">',
         '<th width="2">',
         '<th merge="2">',
         '<th colspan="2">',
     ], 3, 1],

    // ── CSS (7) ───────────────────────────────────────────────────────────

    ['CSS',
     'What is the visual difference between `display: none` and `visibility: hidden`?',
     [
         'They produce identical results',
         '`display: none` removes the element from the layout entirely; `visibility: hidden` hides it but keeps its space',
         '`visibility: hidden` removes the element from the layout; `display: none` keeps its space',
         '`display: none` only works on inline elements',
     ], 1, 1],

    ['CSS',
     'What does the CSS `transition` property do?',
     [
         'Teleports an element to a new position instantly',
         'Creates a looping keyframe animation',
         'Smoothly animates changes to a CSS property over a specified duration',
         'Switches between two different stylesheets',
     ], 2, 1],

    ['CSS',
     'What is the purpose of a CSS `@media` query?',
     [
         'To embed media files such as audio and video',
         'To import an external font from Google Fonts',
         'To query the server for additional styles',
         'To apply different CSS rules based on screen size or device characteristics',
     ], 3, 1],

    ['CSS',
     'What is the `fr` unit in CSS Grid layout?',
     [
         'A fixed pixel measurement used in grid columns',
         'A fractional unit that represents a share of the remaining available space',
         'A unit relative to the font size of the root element',
         'A unit equal to the width of the letter "f"',
     ], 1, 1],

    ['CSS',
     'What does applying `margin: 0 auto` to a block element with a fixed width achieve?',
     [
         'Removes all margins from the element',
         'Adds equal top and bottom margins',
         'Centers the element horizontally within its container',
         'Makes the element full-width',
     ], 2, 1],

    ['CSS',
     'What is a CSS pseudo-class?',
     [
         'A placeholder class used only during development',
         'A selector that targets an element based on a specific state, such as `:hover` or `:focus`',
         'A class that only applies on mobile screens',
         'A CSS custom property (variable)',
     ], 1, 1],

    ['CSS',
     'What happens to content that overflows a container when `overflow: hidden` is applied?',
     [
         'The container becomes invisible',
         'A scrollbar is automatically added',
         'The overflowing content is clipped and not visible outside the container',
         'The content wraps onto a new line',
     ], 2, 1],

    // ── JavaScript (6) ────────────────────────────────────────────────────

    ['JavaScript',
     'What is the difference between `null` and `undefined` in JavaScript?',
     [
         'They are completely identical and interchangeable',
         '`null` intentionally represents no value; `undefined` means a variable was declared but never assigned a value',
         '`undefined` represents no value; `null` means the variable was never declared',
         'Only `undefined` is a primitive; `null` is an object type',
     ], 1, 1],

    ['JavaScript',
     'What does the spread operator (`...`) do when used with an array?',
     [
         'Multiplies every value in the array',
         'Removes duplicate values from the array',
         'Loops through the array and logs each item',
         'Expands the array into individual elements, useful for copying or merging',
     ], 3, 1],

    ['JavaScript',
     'What does `Array.prototype.reduce()` do?',
     [
         'Reduces the array to half its length',
         'Removes the last element and returns it',
         'Filters elements based on a condition',
         'Iterates over every element and accumulates them into a single output value',
     ], 3, 1],

    ['JavaScript',
     'What problem does `async/await` solve in JavaScript?',
     [
         'It runs JavaScript code on multiple CPU threads simultaneously',
         'It makes all variables globally available',
         'It allows asynchronous code to be written in a cleaner, synchronous-looking style',
         'It speeds up synchronous loops',
     ], 2, 1],

    ['JavaScript',
     'What is the difference between `document.querySelector()` and `document.querySelectorAll()`?',
     [
         '`querySelector` only accepts ID selectors; `querySelectorAll` accepts class selectors',
         '`querySelector` returns the first matching element; `querySelectorAll` returns a NodeList of all matches',
         '`querySelectorAll` is deprecated and should not be used',
         'They are identical — both return all matching elements',
     ], 1, 1],

    ['JavaScript',
     'What does `localStorage.setItem("theme", "dark")` do?',
     [
         'Temporarily stores data that is cleared when the browser tab is closed',
         'Sends the value to a server to be stored in a database',
         'Stores the key-value pair in the browser persistently, surviving page refreshes and browser restarts',
         'Encrypts the value before saving it',
     ], 2, 1],
];

// Insert
$stmt = db()->prepare('INSERT INTO qualifying_questions (exam_id,course_tag,question_text,options_json,correct_answer,points,order_index) VALUES (?,?,?,?,?,?,?)');
$inserted = 0;

foreach ($questions as $i => $q) {
    [$tag, $text, $opts, $correct, $points] = $q;
    $stmt->execute([$examId, $tag, $text, json_encode($opts), (string)$correct, $points, $startOrder + $i]);
    $inserted++;
}

$newTotal = (int)db()->query("SELECT COUNT(*) FROM qualifying_questions WHERE exam_id = $examId")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Question Seeder v2</title>
<style>
body{font-family:system-ui,sans-serif;max-width:760px;margin:2rem auto;padding:0 1rem}
.banner{padding:1rem 1.25rem;border-radius:6px;background:#dcfce7;color:#15803d;font-weight:600;margin-bottom:1.5rem}
.info{padding:.75rem 1rem;border-radius:6px;background:#eff6ff;color:#1d4ed8;margin-bottom:1.5rem;font-size:.9rem}
table{width:100%;border-collapse:collapse;font-size:.875rem}
th,td{padding:.6rem .75rem;border:1px solid #e5e7eb;text-align:left}
th{background:#f9fafb;font-weight:600}
.tag{display:inline-block;padding:.2rem .6rem;border-radius:4px;font-size:.7rem;font-weight:700;background:#fef3c7;color:#92400e}
.tag.css{background:#e0e7ff;color:#3730a3}
.tag.js{background:#dcfce7;color:#15803d}
.warn{background:#fef9c3;color:#854d0e;padding:.75rem 1rem;border-radius:6px;margin-top:1.5rem;font-size:.875rem}
</style>
</head>
<body>
<h2>Question Seeder — v2</h2>
<div class="banner">✓ <?= $inserted ?> questions added. Total in exam: <strong><?= $newTotal ?></strong>.</div>
<div class="info">Previously: <?= $currentCount ?> questions &nbsp;→&nbsp; Now: <?= $newTotal ?> questions</div>
<table>
    <thead><tr><th>#</th><th>Tag</th><th>Question</th></tr></thead>
    <tbody>
    <?php foreach ($questions as $i => [$tag, $text]): ?>
    <?php $tagClass = $tag === 'CSS' ? 'css' : ($tag === 'JavaScript' ? 'js' : ''); ?>
    <tr>
        <td><?= $currentCount + $i + 1 ?></td>
        <td><span class="tag <?= $tagClass ?>"><?= htmlspecialchars($tag) ?></span></td>
        <td><?= htmlspecialchars(mb_substr($text, 0, 100)) ?><?= mb_strlen($text) > 100 ? '…' : '' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="warn">⚠ <strong>Delete this file from the server immediately.</strong> Then go to <a href="/admin/qualifying_exam.php">Admin → Final Exam</a> to review all questions.</div>
</body>
</html>
