<?php
/**
 * Final Exam Question Seeder
 * Run ONCE, then DELETE this file from the server.
 * Access: /seed_questions.php?key=hackathon2026seed
 */

define('SEED_PASSWORD', 'hackathon2026seed');

if (($_GET['key'] ?? '') !== SEED_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026seed to the URL.</p>');
}

require_once __DIR__ . '/config/database.php';

// Get or create the exam
$exam = db()->query('SELECT * FROM qualifying_exam WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch();
if (!$exam) {
    db()->exec("INSERT INTO qualifying_exam (title, description, instructions, pass_mark, time_limit, is_active)
        VALUES ('Final Exam',
                'A comprehensive exam covering HTML, CSS, and JavaScript.',
                'Read each question carefully before answering. You may not go back once the exam is submitted. Your camera must remain active throughout.',
                70, 90, 1)");
    $examId = db()->lastInsertId();
} else {
    $examId = $exam['id'];
}

// Check if questions already exist
$existing = (int)db()->query("SELECT COUNT(*) FROM qualifying_questions WHERE exam_id = $examId")->fetchColumn();
if ($existing > 0 && !isset($_GET['force'])) {
    die("<h3>Questions already exist ($existing found).</h3><p>Add <code>&force=1</code> to the URL to re-seed (this will add duplicates).</p>");
}

// ── Questions ──────────────────────────────────────────────────────────────
// Format: [course_tag, question_text, [optA, optB, optC, optD], correct_index (0-3), points]
$questions = [

    // ── HTML ──────────────────────────────────────────────────────────────
    ['HTML', 'Which semantic HTML element should wrap the primary navigation links of a webpage?',
        ['<nav>', '<menu>', '<header>', '<links>'], 0, 1],

    ['HTML', 'What does the `defer` attribute do when added to a `<script>` tag?',
        ['Loads the script from a CDN','Runs the script after the HTML is fully parsed','Makes the script load in parallel and execute immediately','Prevents the script from running on mobile'], 1, 1],

    ['HTML', 'Which HTML input type renders a native date picker in modern browsers?',
        ['type="calendar"', 'type="datetime"', 'type="date"', 'type="picker"'], 2, 1],

    ['HTML', 'What is the correct element to use for a self-contained piece of content such as a blog post or news article?',
        ['<section>', '<div>', '<aside>', '<article>'], 3, 1],

    ['HTML', 'What is the purpose of the `<meta name="viewport" content="width=device-width, initial-scale=1">` tag?',
        ['Sets the page language for screen readers','Controls how the page scales and fits on mobile devices','Prevents search engines from indexing the page','Defines the character encoding of the document'], 1, 1],

    ['HTML', 'An `<img>` tag\'s `alt` attribute is missing. What is the primary problem with this?',
        ['The image will not load','The page layout will break','Screen readers and users with broken images have no description of the content','The browser will display a broken icon'], 2, 1],

    ['HTML', 'Which attribute correctly prevents a user from submitting a form without filling in a field?',
        ['mandatory', 'validate="true"', 'required', 'no-skip'], 2, 1],

    ['HTML', 'What is the difference between `<section>` and `<div>` in HTML5?',
        ['They are identical in every way','`<section>` is purely for layout; `<div>` has semantic meaning','`<section>` carries semantic meaning (a thematic grouping); `<div>` has no semantic meaning','`<div>` is deprecated in HTML5'], 2, 1],

    ['HTML', 'Which HTML element and attribute combination opens a link in a new browser tab?',
        ['<a href="#" target="_blank">', '<a href="#" open="tab">', '<a href="#" window="new">', '<link href="#" target="new">'], 0, 1],

    ['HTML', 'A form submits data via a GET request by default. Which attribute changes it to POST?',
        ['action="post"', 'type="post"', 'send="post"', 'method="post"'], 3, 1],

    // ── CSS ───────────────────────────────────────────────────────────────
    ['CSS', 'What does `box-sizing: border-box` change about how an element\'s size is calculated?',
        ['It hides the border','Padding and border are included within the declared width and height','It removes margin from the calculation','It makes the element a block-level box'], 1, 1],

    ['CSS', 'Given these selectors: `p { }`, `.title { }`, `#hero { }`, and `style=""` inline — what is their specificity order from lowest to highest?',
        ['p → .title → #hero → inline', '#hero → .title → p → inline', 'inline → #hero → p → .title', '.title → p → #hero → inline'], 0, 1],

    ['CSS', 'In Flexbox, which property controls how items are aligned along the CROSS axis?',
        ['justify-content', 'flex-wrap', 'align-items', 'flex-direction'], 2, 1],

    ['CSS', 'What does `position: absolute` position an element relative to?',
        ['Always the top-left corner of the browser window','The nearest ancestor that has a position value other than static','The document <body> element','Its adjacent sibling'], 1, 1],

    ['CSS', 'Which CSS unit is always relative to the font size of the root `<html>` element, regardless of nesting?',
        ['em', 'rem', 'vw', 'ex'], 1, 1],

    ['CSS', 'How do you select only the DIRECT `<p>` children of a `<div>`, not descendants deeper in the tree?',
        ['div p', 'div + p', 'div ~ p', 'div > p'], 3, 1],

    ['CSS', 'For `z-index` to have any effect on an element, what must be true?',
        ['The element must have display: flex','The element must have a position value other than static','The element must have a defined width and height','The element must have overflow: hidden'], 1, 1],

    ['CSS', 'What does `flex: 1` expand to in shorthand notation?',
        ['flex-grow: 1; flex-shrink: 0; flex-basis: auto','flex-grow: 1; flex-shrink: 1; flex-basis: 0%','flex-grow: 0; flex-shrink: 1; flex-basis: 100%','flex-grow: 1; flex-shrink: 1; flex-basis: auto'], 1, 1],

    ['CSS', 'Which CSS property would you use to prevent text from wrapping onto a new line?',
        ['overflow: hidden', 'text-overflow: clip', 'white-space: nowrap', 'word-break: keep-all'], 2, 1],

    ['CSS', 'What is CSS specificity used for?',
        ['Measuring how fast a CSS rule is applied','Determining which CSS rule wins when multiple rules target the same element','Setting the order in which stylesheets are loaded','Validating CSS syntax'], 1, 1],

    // ── JavaScript ────────────────────────────────────────────────────────
    ['JavaScript', 'What does `typeof null` return in JavaScript?',
        ['"null"', '"undefined"', '"object"', '"boolean"'], 2, 1],

    ['JavaScript', 'What is the key difference between `==` and `===` in JavaScript?',
        ['=== is always faster than ==','== checks value only; === checks both value AND type','== checks value and type; === checks value only','There is no practical difference'], 1, 1],

    ['JavaScript', 'What will this code log? `console.log(0.1 + 0.2 === 0.3)`',
        ['true', 'false', 'undefined', 'NaN'], 1, 1],

    ['JavaScript', 'Which array method returns a NEW array containing only the elements that pass a test function?',
        ['forEach()', 'map()', 'filter()', 'reduce()'], 2, 1],

    ['JavaScript', 'What is a JavaScript closure?',
        ['A method to close a modal window','A function that retains access to variables from its outer scope even after the outer function has returned','A built-in error handling technique','A way to terminate a loop early'], 1, 1],

    ['JavaScript', 'What does `event.preventDefault()` do inside an event handler?',
        ['Stops the event from reaching parent elements','Prevents the browser from performing its built-in default action for that event','Removes all event listeners from the element','Delays the event handler by 500ms'], 1, 1],

    ['JavaScript', 'What is the difference between `let` and `var` when declaring variables?',
        ['`let` is function-scoped; `var` is block-scoped','`let` is block-scoped; `var` is function-scoped','`let` cannot be reassigned; `var` can','`var` is newer and preferred in modern JavaScript'], 1, 1],

    ['JavaScript', 'Which of the following correctly adds a click event listener to a button with id="btn"?',
        ['document.getElementById("btn").addEvent("click", fn)','document.getElementById("btn").on("click", fn)','document.getElementById("btn").addEventListener("click", fn)','document.querySelector("#btn").attachEvent("click", fn)'], 2, 1],

    ['JavaScript', 'What does `JSON.stringify({ name: "Ada" })` return?',
        ['An object with a name property','The string \'{"name":"Ada"}\'','undefined','A JSON file'], 1, 1],

    ['JavaScript', 'What is the output of the following code?\n`const arr = [1, 2, 3];\nconsole.log(arr.map(x => x * 2));`',
        ['[1, 2, 3]', '[2, 4, 6]', 'undefined', '6'], 1, 1],
];

// Insert questions
$stmt = db()->prepare('INSERT INTO qualifying_questions (exam_id, course_tag, question_text, options_json, correct_answer, points, order_index) VALUES (?,?,?,?,?,?,?)');
$inserted = 0;

foreach ($questions as $i => $q) {
    [$tag, $text, $opts, $correct, $points] = $q;
    $stmt->execute([$examId, $tag, $text, json_encode($opts), (string)$correct, $points, $i + 1]);
    $inserted++;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Question Seeder</title>
<style>
body{font-family:system-ui,sans-serif;max-width:700px;margin:2rem auto;padding:0 1rem}
.banner{padding:1rem 1.25rem;border-radius:6px;background:#dcfce7;color:#15803d;font-weight:600;margin-bottom:1.5rem}
table{width:100%;border-collapse:collapse;font-size:.875rem}
th,td{padding:.6rem .75rem;border:1px solid #e5e7eb;text-align:left}
th{background:#f9fafb;font-weight:600}
.tag{display:inline-block;padding:.2rem .6rem;border-radius:4px;font-size:.7rem;font-weight:700;background:#fef3c7;color:#92400e}
.warn{background:#fef9c3;color:#854d0e;padding:.75rem 1rem;border-radius:6px;margin-top:1.5rem;font-size:.875rem}
</style>
</head>
<body>
<h2>Question Seeder</h2>
<div class="banner">✓ <?= $inserted ?> questions inserted into exam ID <?= $examId ?>.</div>
<table>
    <thead><tr><th>#</th><th>Tag</th><th>Question</th></tr></thead>
    <tbody>
    <?php foreach ($questions as $i => [$tag, $text]): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td><span class="tag"><?= htmlspecialchars($tag) ?></span></td>
        <td><?= htmlspecialchars(mb_substr($text, 0, 90)) ?><?= mb_strlen($text) > 90 ? '…' : '' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="warn">⚠ <strong>Delete this file from the server immediately.</strong> Then go to <a href="/admin/qualifying_exam.php">Admin → Final Exam</a> to review and edit the questions.</div>
</body>
</html>
